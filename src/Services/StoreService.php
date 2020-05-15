<?php

namespace Foodsharing\Services;

use Carbon\Carbon;
use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Modules\Store\DTO\StoreForTopbarMenu;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\TeamStatus;
use Symfony\Contracts\Translation\TranslatorInterface;

class StoreService
{
	private $messageGateway;
	private $storeGateway;
	private $translator;
	const MAX_SLOTS_PER_PICKUP = 10;
	// status constants for getAvailablePickupStatus
	const STATUS_RED_TODAY_TOMORROW = 3;
	const STATUS_ORANGE_3_DAYS = 2;
	const STATUS_YELLOW_5_DAYS = 1;
	const STATUS_GREEN = 0;

	public function __construct(MessageGateway $messageGateway, StoreGateway $storeGateway, TranslatorInterface $translator)
	{
		$this->messageGateway = $messageGateway;
		$this->storeGateway = $storeGateway;
		$this->translator = $translator;
	}

	/**
	 * Changes the number of total slots for a pickup. Implements the logic to take care to
	 *   * not remove a slot where somebody is signed up for
	 *   * handle transition between regular and onetime pickup
	 *   * (does not convert additional back to regular as the gain is little).
	 */
	public function changePickupSlots(int $storeId, \DateTimeInterface $date, int $newTotalSlots): bool
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

	/**
	 * Returns the time of the next available pickup slot or null if none is available up to the
	 * given maximum date.
	 *
	 * @param Carbon $maxDate end of date range
	 *
	 * @return \DateTime the slot's time or null
	 */
	public function getNextAvailablePickupTime(int $storeId, Carbon $maxDate): ?\DateTime
	{
		if ($maxDate < Carbon::now()) {
			return null;
		}

		$pickupSlots = $this->storeGateway->getPickupSlots($storeId, Carbon::now(), $maxDate, $maxDate);

		$minimumDate = null;
		foreach ($pickupSlots as $slot) {
			if ($slot['isAvailable'] && (is_null($minimumDate) || $slot['date'] < $minimumDate)) {
				$minimumDate = $slot['date'];
			}
		}

		return $minimumDate;
	}

	/**
	 * Returns the available pickup status of a store: 1, 2, or 3 if there is a free pickup slot in the next day,
	 * three days, or five days, respectively. Returns 0 if there is no free slot in the next five days.
	 */
	public function getAvailablePickupStatus(int $storeId): int
	{
		$availableDate = $this->getNextAvailablePickupTime($storeId, Carbon::tomorrow()->addDays(5));
		if (is_null($availableDate)) {
			return self::STATUS_GREEN;
		} elseif ($availableDate < Carbon::tomorrow()->addDay()) {
			return self::STATUS_RED_TODAY_TOMORROW;
		} elseif ($availableDate < Carbon::tomorrow()->addDays(3)) {
			return self::STATUS_ORANGE_3_DAYS;
		} else {
			return self::STATUS_YELLOW_5_DAYS;
		}
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

	public function setStoreNameInConversations(int $storeId, string $storeName): void
	{
		if ($tcid = $this->storeGateway->getBetriebConversation($storeId, false)) {
			$team_conversation_name = $this->translator->trans('store.team_conversation_name', ['{name}' => $storeName]);
			$this->messageGateway->renameConversation($tcid, $team_conversation_name);
		}
		if ($scid = $this->storeGateway->getBetriebConversation($storeId, true)) {
			$springer_conversation_name = $this->translator->trans('store.springer_conversation_name', ['{name}' => $storeName]);
			$this->messageGateway->renameConversation($scid, $springer_conversation_name);
		}
	}

	/**
	 * @return StoreForTopbarMenu[]
	 */
	public function getFilteredStoresForUser(int $userId): array
	{
		$filteredStoresForUser = $this->storeGateway->listFilteredStoresForFoodsaver($userId);

		foreach ($filteredStoresForUser as $store) {
			// add info about the next free pickup slot to the store
			$store->pickupStatus = $this->getAvailablePickupStatus($store->id);
		}

		return $filteredStoresForUser;
	}
}
