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
}
