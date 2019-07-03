<?php

namespace Foodsharing\Services;

use Carbon\Carbon;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\TeamStatus;

class StoreService
{
	private $storeGateway;
	const MAX_SLOTS_PER_PICKUP = 10;

	public function __construct(StoreGateway $storeGateway)
	{
		$this->storeGateway = $storeGateway;
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

	public function pickupSlotAvailable(int $storeId, Carbon $pickupDate, int $fsId = null): bool
	{
		if ($pickupDate < Carbon::now()) {
			/* do not allow signing up for past pickups */
			return false;
		}

		$pickupSlots = $this->storeGateway->getPickupSlots($storeId, $pickupDate, $pickupDate, $pickupDate);
		if (count($pickupSlots) == 1 && $pickupSlots[0]['isAvailable']) {
			/* expect a free slot */
			if ($fsId) {
				if (!empty(array_filter($pickupSlots[0]['occupiedSlots'],
					function ($e) use ($fsId) { return $e['foodsaverId'] === $fsId; }))) {
					/* when a user is provided, that user must not already be signed up */
					return false;
				}
			}

			return true;
		}

		return false;
	}

	public function joinPickup(int $storeId, Carbon $date, int $fsId, int $issuerId = null): bool
	{
		$confirmed = $this->pickupIsPreconfirmed($storeId, $issuerId);

		/* Never occupy more slots than available */
		if (!$this->pickupSlotAvailable($storeId, $date, $fsId)) {
			throw new \DomainException('No pickup slot available');
		}

		$this->storeGateway->addFetcher($fsId, $storeId, $date, $confirmed);

		return $confirmed;
	}

	private function pickupIsPreconfirmed(int $storeId, int $issuerId = null): bool
	{
		if ($issuerId) {
			return $this->storeGateway->getUserTeamStatus($issuerId, $storeId) === TeamStatus::Coordinator;
		}

		return false;
	}
}
