<?php

namespace Foodsharing\Services;

use Carbon\Carbon;
use Foodsharing\Modules\Store\StoreGateway;

class StoreService
{
	private $storeGateway;

	public function __construct(StoreGateway $storeGateway)
	{
		$this->storeGateway = $storeGateway;
	}

	public function signupForPickup(int $fsId, int $storeId, Carbon $pickupDate, bool $confirmed = false)
	{
		/* Never occupy more slots than available */
		if (!$this->pickupSlotAvailable($storeId, $pickupDate)) {
			return false;
		}

		/* never signup a person twice */
		if (!empty(array_filter($this->storeGateway->getPickupSignupsForDate($storeId, $pickupDate),
			function ($e) use ($fsId) { return $e['foodsaver_id'] === $fsId; }))) {
			return false;
		}

		$this->storeGateway->addFetcher($fsId, $storeId, $pickupDate, $confirmed);
		if (!$confirmed) {
			$this->storeGateway->updateBellNotificationForBiebs($storeId, true);
		}

		return true;
	}

	public function pickupSlotAvailable(int $storeId, Carbon $pickupDate): bool
	{
		if ($pickupDate < Carbon::now()) {
			/* do not allow signing up for past pickups */
			return false;
		}

		$pickupSlots = $this->listPickupSlots($storeId, $pickupDate, $pickupDate, $pickupDate);
		if (count($pickupSlots) == 1 && $pickupSlots[0]['available']) {
			return true;
		}

		return false;
	}

	private function realMod(int $a, int $b)
	{
		$res = $a % $b;
		if ($res < 0) {
			return $res += abs($b);
		}

		return $res;
	}

	/**
	 * @param int $storeId
	 * @param \DateTime $from DateRange start for all slots. Now if empty.
	 * @param \DateTime $to DateRange for regular slots - future pickup interval if empty
	 * @param \DateTime $additionalTo DateRange for additional slots to be taken into account
	 *
	 * @return array
	 */
	public function listPickupSlots(int $storeId, ?Carbon $from = null, ?Carbon $to = null, ?Carbon $additionalTo = null): array
	{
		$intervalFuturePickupSignup = $this->storeGateway->getFutureRegularPickupInterval($storeId);
		$from = $from ?? Carbon::now();
		$to = $to ?? Carbon::now()->add($intervalFuturePickupSignup);
		$regularSlots = $this->storeGateway->getRegularPickupSlots($storeId);
		$additionalSlots = $this->storeGateway->getSinglePickupSlotsForRange($storeId, $from, $additionalTo);
		$signups = $this->storeGateway->getPickupSignupsForDateRange($storeId, $from, $to);

		$slots = [];
		foreach ($regularSlots as $slot) {
			$date = $from->copy();
			$date->addDays($this->realMod($slot['dow'] - $date->format('w'), 7));
			$date->setTimeFromTimeString($slot['time']);
			while ($date <= $to) {
				if (empty(array_filter($additionalSlots, function ($e) use ($date) {
					return $date == $e['date'];
				}))) {
					/* only take this regular slot into account when there is no manual slot for the same time */
					$occupiedSlots = array_map(
						function ($e) {
							return ['foodsaver_id' => $e['foodsaver_id'], 'confirmed' => (bool)$e['confirmed']];
						},
						array_filter($signups,
							function ($e) use ($date) {
								return $date == $e['date'];
							}
						)
					);
					$isAvailable =
						$date > Carbon::now() &&
						$date < Carbon::now()->add($intervalFuturePickupSignup) &&
						$slot['fetcher'] > count($occupiedSlots);
					$slots[] = [
						'date' => $date,
						'totalSlots' => $slot['fetcher'],
						'occupiedSlots' => array_values($occupiedSlots),
						'available' => $isAvailable
					];
				}

				$date = $date->copy()->addDays(7);
			}
		}
		foreach ($additionalSlots as $slot) {
			$occupiedSlots = array_filter($signups, function ($e) use ($slot) {
				return $slot['date'] == $e['date'];
			});
			/* Additional slots are always in the future available for signups */
			$isAvailable =
				$slot['date'] > Carbon::now() &&
				$slot['fetcher'] > count($occupiedSlots);
			$slots[] = [
				'date' => $slot['date'],
				'totalSlots' => $slot['fetcher'],
				'occupiedSlots' => array_values($occupiedSlots),
				'available' => $isAvailable];
		}

		return $slots;
	}
}
