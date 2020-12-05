<?php

namespace Foodsharing\Modules\Store;

use Carbon\Carbon;
use DateTime;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Store\Milestone;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\DTO\StoreForTopbarMenu;
use Symfony\Contracts\Translation\TranslatorInterface;

class StoreTransactions
{
	private MessageGateway $messageGateway;
	private PickupGateway $pickupGateway;
	private StoreGateway $storeGateway;
	private TranslatorInterface $translator;
	private BellGateway $bellGateway;
	private FoodsaverGateway $foodsaverGateway;
	private RegionGateway $regionGateway;
	private Session $session;
	const MAX_SLOTS_PER_PICKUP = 10;
	// status constants for getAvailablePickupStatus
	const STATUS_RED_TODAY_TOMORROW = 3;
	const STATUS_ORANGE_3_DAYS = 2;
	const STATUS_YELLOW_5_DAYS = 1;
	const STATUS_GREEN = 0;

	public function __construct(
		MessageGateway $messageGateway,
		PickupGateway $pickupGateway,
		StoreGateway $storeGateway,
		TranslatorInterface $translator,
		BellGateway $bellGateway,
		FoodsaverGateway $foodsaverGateway,
		RegionGateway $regionGateway,
		Session $session
	) {
		$this->messageGateway = $messageGateway;
		$this->pickupGateway = $pickupGateway;
		$this->storeGateway = $storeGateway;
		$this->translator = $translator;
		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionGateway = $regionGateway;
		$this->session = $session;
	}

	/**
	 * Changes the number of total slots for a pickup. Implements the logic to take care to
	 *   * not remove a slot where somebody is signed up for
	 *   * handle transition between regular and onetime pickup
	 *   * (does not convert additional back to regular as the gain is little).
	 */
	public function changePickupSlots(int $storeId, \DateTimeInterface $date, int $newTotalSlots): bool
	{
		$occupiedSlots = count($this->pickupGateway->getPickupSignupsForDate($storeId, $date));
		$pickups = $this->pickupGateway->getOnetimePickupsForRange($storeId, $date, $date);
		if (!$pickups) {
			if ($newTotalSlots >= 0 && $newTotalSlots <= self::MAX_SLOTS_PER_PICKUP && $newTotalSlots >= $occupiedSlots) {
				$this->pickupGateway->addOnetimePickup($storeId, $date, $newTotalSlots);
			} else {
				return false;
			}
		} else {
			if ($newTotalSlots >= 0 && $newTotalSlots <= self::MAX_SLOTS_PER_PICKUP && $newTotalSlots >= $occupiedSlots) {
				$this->pickupGateway->updateOnetimePickupTotalSlots($storeId, $date, $newTotalSlots);
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

		$pickupSlots = $this->pickupGateway->getPickupSlots($storeId, $pickupDate, $pickupDate, $pickupDate);
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

		$pickupSlots = $this->pickupGateway->getPickupSlots($storeId, Carbon::now(), $maxDate, $maxDate);

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

		$this->pickupGateway->addFetcher($fsId, $storeId, $date, $confirmed);

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
	public function getFilteredStoresForUser(?int $userId): array
	{
		if ($userId === null) {
			return [];
		}
		$filteredStoresForUser = $this->storeGateway->listFilteredStoresForFoodsaver($userId);

		foreach ($filteredStoresForUser as $store) {
			// add info about the next free pickup slot to the store
			$store->pickupStatus = $this->getAvailablePickupStatus($store->id);
		}

		return $filteredStoresForUser;
	}

	/**
	 * Accepts a user's request to join a store. This creates a bell notification for that user, adds an entry
	 * to the store log and the store's wall, and makes sure the user is in the store's region.
	 */
	public function acceptStoreRequest(int $storeId, int $userId): void
	{
		// add user to the team and to the team's conversation
		$this->storeGateway->addUserToTeam($storeId, $userId);
		if ($scid = $this->storeGateway->getBetriebConversation($storeId, true)) {
			$this->messageGateway->deleteUserFromConversation($scid, $userId);
		}
		if ($tcid = $this->storeGateway->getBetriebConversation($storeId, false)) {
			$this->messageGateway->addUserToConversation($tcid, $userId);
		}

		// add an entry to the store log and a note the store wall
		$this->storeGateway->addStoreLog($storeId, $this->session->id(), $userId, null, StoreLogAction::REQUEST_APPROVED);
		$this->storeGateway->add_betrieb_notiz([
			'foodsaver_id' => $userId,
			'betrieb_id' => $storeId,
			'text' => '{ACCEPT_REQUEST}',
			'zeit' => date('Y-m-d H:i:s'),
			'milestone' => Milestone::ACCEPTED,
		]);

		// create bell for the user who is accepted
		$storeName = $this->storeGateway->getStoreName($storeId);
		$bellData = Bell::create('store_request_accept_title', 'store_request_accept', 'fas fa-user-check', [
			'href' => '/?page=fsbetrieb&id=' . $storeId
		], [
			'user' => $this->session->user('name'),
			'name' => $storeName
		], 'store-arequest-' . $userId);
		$this->bellGateway->addBell($userId, $bellData);

		// add the user to the store's region
		$regionId = $this->storeGateway->getStoreRegionId($storeId);
		$this->regionGateway->linkBezirk($userId, $regionId);
	}

	/**
	 * Removes (denies) a user's request for a store and creates a bell notification for that user.
	 */
	public function removeStoreRequest(int $storeId, int $userId): void
	{
		// userId = affected user, sessionId = active user
		// => don't add a bell notification if the request was withdrawn by the user
		if ($userId !== $this->session->id()) {
			$this->createBellNotificationForRejectedApplication($storeId, $userId);
		}

		$this->storeGateway->removeUserFromTeam($storeId, $userId);
	}

	private function createBellNotificationForRejectedApplication(int $storeId, int $userId): void
	{
		$storeName = $this->storeGateway->getStoreName($storeId);

		$bellData = Bell::create('store_request_deny_title', 'store_request_deny', 'fas fa-user-times', [
			'href' => '/?page=fsbetrieb&id=' . $storeId
		], [
			'name' => $storeName,
		], 'store-drequest-' . $userId);

		$this->bellGateway->addBell($userId, $bellData);
	}

	public function createKickMessage(int $foodsaverId, int $storeId, DateTime $pickupDate, ?string $message = null): string
	{
		$fs = $this->foodsaverGateway->getFoodsaver($foodsaverId);
		$store = $this->storeGateway->getBetrieb($storeId);

		$salutation = $this->translator->trans('salutation.' . $fs['geschlecht']) . ' ' . $fs['name'];
		$mandatoryMessage = $this->translator->trans('pickup.kick_message', [
			'{storeName}' => $store['name'],
			'{date}' => date('d.m.Y H:i', $pickupDate->getTimestamp())
		]);
		$optionalMessage = empty($message) ? '' : ("\n\n" . $message);
		$footer = $this->translator->trans('pickup.kick_message_footer');

		return $salutation . ",\n" . $mandatoryMessage . $optionalMessage . "\n\n" . $footer;
	}
}
