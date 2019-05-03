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

	public function signupForPickup(int $fsId, int $storeId, \DateTime $pickupDate, bool $confirmed = false)
	{
		if (!$this->pickupSlotAvailable($storeId, $pickupDate)) {
			return false;
		}

		if (in_array(['foodsaver_id' => $fsId], $this->storeGateway->getPickupSignupsForDate($storeId, $pickupDate), true)) {
			return false;
		}

		$this->storeGateway->addFetcher($fsId, $storeId, $pickupDate, $confirmed);
		if (!$confirmed) {
			$this->storeGateway->updateBellNotificationForBiebs($storeId, true);
		}

		return true;
	}

	public function pickupSlotAvailable(int $storeId, \DateTime $pickupDate): bool
	{
		$signUps = $this->storeGateway->getPickupSignupsForDate($storeId, $pickupDate);
		$regularSlots = $this->storeGateway->getRegularPickupSlots($storeId);
		$additionalSlots = $this->storeGateway->getSinglePickupSlots($storeId, $pickupDate);
		$intervalFuturePickupSignup = $this->storeGateway->getFutureRegularPickupInterval($storeId);

		$sum = function ($c, $e) {
			return $c + $e['fetcher'];
		};
		$numRegularSlots = array_reduce($regularSlots, $sum, 0);
		$numAdditionalSlots = array_reduce($additionalSlots, $sum, 0);

		if ($pickupDate < Carbon::now()) {
			/* do not allow signing up for past pickups */
			return false;
		}

		if ($pickupDate > Carbon::now()->add($intervalFuturePickupSignup)) {
			/* do only allow signing up for very future pickups for single dates */
			return ($numAdditionalSlots - count($signUps)) > 0;
		}

		/* in between now and regular pickup interval: allow signing up for single and regular dates */
		return ($numRegularSlots + $numAdditionalSlots - count($signUps)) > 0;
	}
}
