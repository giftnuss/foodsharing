<?php

namespace Foodsharing\Services;

use Carbon\Carbon;
use Foodsharing\Modules\Store\StoreGateway;

class StoreService
{
	private $storeGateway;
	const MAX_SLOTS_PER_PICKUP = 10;

	public function __construct(StoreGateway $storeGateway)
	{
		$this->storeGateway = $storeGateway;
	}

	public function joinPickup(int $fsId, int $storeId, Carbon $pickupDate, bool $confirmed = false)
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

		return true;
	}

	public function pickupSlotAvailable(int $storeId, Carbon $pickupDate): bool
	{
		if ($pickupDate < Carbon::now()) {
			/* do not allow signing up for past pickups */
			return false;
		}

		$pickupSlots = $this->storeGateway->getPickupSlots($storeId, $pickupDate, $pickupDate, $pickupDate);
		if (count($pickupSlots) == 1 && $pickupSlots[0]['available']) {
			return true;
		}

		return false;
	}

	/**
	 * Remove a user from a pickup.
	 *
	 * @return bool if a user was deleted from the pickup
	 */
	public function leavePickup(int $storeId, \DateTime $date, int $fsId)
	{
		return $this->storeGateway->removeFetcher($fsId, $storeId, $date) > 0;
	}

	public function confirmPickup(int $storeId, \DateTime $date, int $fsId): bool
	{
		return $this->storeGateway->confirmFetcher($fsId, $storeId, $date) === 1;
	}

	/**
	 * Changes the number of total slots for a pickup. Implements the logic to take care to
	 *   * not remove a slot where somebody is signed up for
	 *   * handle transition between regular and onetime pickup
	 *   * (does not convert additional back to regular as the gain is little).
	 */
	public function changePickupSlots(int $storeId, Carbon $date, int $newTotalSlots): bool
	{
		$occupiedSlots = count($this->storeGateway->getPickupSignupsForDate($storeId, $date));
		$pickups = $this->storeGateway->getOnetimePickupsForRange($storeId, $date, $date);
		if (!$pickups) {
			if ($newTotalSlots >= 0 && $newTotalSlots <= self::MAX_SLOTS_PER_PICKUP && $newTotalSlots >= $occupiedSlots) {
				$this->storeGateway->addOnetimePickup($storeId, $date, $newTotalSlots);
			} else {
				return false;
			}
		} else {
			if ($newTotalSlots >= 0 && $newTotalSlots <= self::MAX_SLOTS_PER_PICKUP && $newTotalSlots >= $occupiedSlots) {
				$this->storeGateway->updateOnetimePickupTotalSlots($storeId, $date, $newTotalSlots);
			} else {
				return false;
			}
		}

		return true;
	}

	public function addPickupSlot(int $storeId, \DateTime $date): bool
	{
		return $this->changePickupSlots($storeId, $date, 1);
	}

	public function removePickupSlot(int $storeId, \DateTime $date): bool
	{
		return $this->changePickupSlots($storeId, $date, -1);
	}
}
