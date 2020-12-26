<?php

namespace Foodsharing\Modules\Store;

use Carbon\Carbon;
use DateTime;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Bell\BellType;
use Foodsharing\Modules\Core\DBConstants\Store\Milestone;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Core\DBConstants\StoreTeam\MembershipStatus;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\DTO\CreateStoreData;
use Foodsharing\Modules\Store\DTO\Store;
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

	public function createStore(array $legacyGlobalData): int
	{
		$store = new CreateStoreData();
		$store->name = $legacyGlobalData['name'];
		$store->regionId = $legacyGlobalData['bezirk_id'];
		$store->lat = floatval($legacyGlobalData['lat']);
		$store->lon = floatval($legacyGlobalData['lon']);
		$store->str = $legacyGlobalData['str'];
		$store->zip = $legacyGlobalData['plz'];
		$store->city = $legacyGlobalData['stadt'];
		$store->createdAt = Carbon::now();
		$store->updatedAt = $store->createdAt;

		$storeId = $this->storeGateway->addStore($store);
		$managerId = $this->session->id();

		$this->storeGateway->addStoreManager($storeId, $managerId);
		$this->createTeamConversations($storeId, $managerId);

		return $storeId;
	}

	public function updateAllStoreData(int $storeId, array $legacyGlobalData): bool
	{
		$this->storeGateway->setGroceries($storeId, $legacyGlobalData['lebensmittel'] ?? []);

		$store = new Store();

		$store->id = $storeId;
		$store->name = $legacyGlobalData['name'];
		$store->regionId = intval($legacyGlobalData['bezirk_id']);

		$address = $legacyGlobalData['str'];
		if ($legacyGlobalData['hsnr'] ?? '') {
			$address .= ' ' . $legacyGlobalData['hsnr'];
		}
		$store->lat = floatval($legacyGlobalData['lat']);
		$store->lon = floatval($legacyGlobalData['lon']);
		$store->str = $address;
		$store->zip = $legacyGlobalData['plz'];
		$store->city = $legacyGlobalData['stadt'];

		$store->publicInfo = $legacyGlobalData['public_info'];
		$store->publicTime = intval($legacyGlobalData['public_time']);

		$store->categoryId = intval($legacyGlobalData['betrieb_kategorie_id']);
		$store->chainId = intval($legacyGlobalData['kette_id']);
		$store->cooperationStatus = intval($legacyGlobalData['betrieb_status_id']);

		$store->description = $legacyGlobalData['besonderheiten'];

		$store->contactName = $legacyGlobalData['ansprechpartner'];
		$store->contactPhone = $legacyGlobalData['telefon'];
		$store->contactFax = $legacyGlobalData['fax'];
		$store->contactEmail = $legacyGlobalData['email'];
		$store->cooperationStart = null;
		if (!empty($legacyGlobalData['begin'])) {
			$store->cooperationStart = Carbon::createFromFormat('Y-m-d', $legacyGlobalData['begin']);
		}
		$store->calendarInterval = intval($legacyGlobalData['prefetchtime']);
		$store->weight = intval($legacyGlobalData['abholmenge']);
		$store->effort = intval($legacyGlobalData['ueberzeugungsarbeit']);
		$store->publicity = intval($legacyGlobalData['presse']);
		$store->sticker = intval($legacyGlobalData['sticker']);

		$store->updatedAt = Carbon::now();

		$this->storeGateway->updateStoreData($store->id, $store);

		return true;
	}

	/**
	 * Changes the number of total slots for a pickup. Implements the logic to take care to
	 *   * not remove a slot where somebody is signed up for
	 *   * handle transition between regular and onetime pickup
	 *   * (does not convert additional back to regular as the gain is little).
	 */
	public function changePickupSlots(int $storeId, \DateTimeInterface $date, int $newTotalSlots): bool
	{
		if ($newTotalSlots < 0 || $newTotalSlots > self::MAX_SLOTS_PER_PICKUP) {
			return false;
		}

		$occupiedSlots = count($this->pickupGateway->getPickupSignupsForDate($storeId, $date));

		// cannot remove excess slots if people are signed into them
		if ($newTotalSlots < $occupiedSlots) {
			return false;
		}

		$filledOnetimeSlots = $this->pickupGateway->getOnetimePickups($storeId, $date);

		if ($filledOnetimeSlots) {
			$this->pickupGateway->updateOnetimePickupTotalSlots($storeId, $date, $newTotalSlots);
		} else {
			$this->pickupGateway->addOnetimePickup($storeId, $date, $newTotalSlots);
		}

		return true;
	}

	/**
	 * Checks whether there are slots available to sign into for one specific pickupDate in a store.
	 *
	 * @param ?int $fsId Check whether this specific user could sign into a slot for this date
	 *
	 * @return int 0 if no available slot, else the number of *total* slots (NOT available slots) for this date
	 */
	public function totalSlotsIfPickupSlotAvailable(int $storeId, Carbon $pickupDate, ?int $fsId = null): int
	{
		// do not allow signing up for past pickups
		if ($pickupDate < Carbon::now()) {
			return 0;
		}

		$pickupSlots = $this->pickupGateway->getPickupSlots($storeId, $pickupDate, $pickupDate, $pickupDate);

		// expect exactly one pickup for this "range" query
		if (count($pickupSlots) === 1) {
			$pickup = $pickupSlots[0];
		} else {
			return 0;
		}

		// check if there are any free slots
		if (!$pickup['isAvailable']) {
			return 0;
		}

		// when a user is provided, that user must not already be signed up
		if ($fsId) {
			$signedUpFoodsaverIds = array_column($pickup['occupiedSlots'], 'foodsaverId');
			if (in_array($fsId, $signedUpFoodsaverIds)) {
				return 0;
			}
		}

		return $pickup['totalSlots'];
	}

	/**
	 * Returns the time of the next available pickup slot or null if none is available up to the
	 * given maximum date.
	 *
	 * @param Carbon $maxDate end of date range
	 *
	 * @return \DateTime the slot's time or null
	 */
	public function getNextAvailablePickupTime(int $storeId, Carbon $maxDate): ?DateTime
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
		if ($totalSlots = $this->totalSlotsIfPickupSlotAvailable($storeId, $date, $fsId)) {
			$this->pickupGateway->addFetcher($fsId, $storeId, $date, $confirmed);
			// [#860] convert to manual slot, so they don't vanish when changing the schedule
			$this->changePickupSlots($storeId, $date, $totalSlots);
		} else {
			throw new \DomainException('No pickup slot available');
		}

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

	public function requestStoreTeamMembership(int $storeId, int $userId): void
	{
		$this->storeGateway->addStoreRequest($storeId, $userId);

		$this->storeGateway->addStoreLog($storeId, $userId, null, null, StoreLogAction::REQUEST_TO_JOIN);

		$this->notifyStoreManagersAboutRequest($storeId, $userId);
	}

	/**
	 * Accepts a user's request to join a store, and moves the user to the standby team if desired.
	 * This creates a bell notification for that user, adds an entry to the store log,
	 * and makes sure the user is in the store's region.
	 *
	 * @param bool $moveToStandby if true, place the new member on the standby list instead of the regular store team
	 */
	public function acceptStoreRequest(int $storeId, int $userId, bool $moveToStandby = false): void
	{
		$this->addUserToStore($storeId, $userId, $moveToStandby);

		$this->storeGateway->addStoreLog($storeId, $this->session->id(), $userId, null, StoreLogAction::REQUEST_APPROVED);

		$actionType = $moveToStandby ? StoreLogAction::MOVED_TO_JUMPER : StoreLogAction::REQUEST_APPROVED;
		$this->triggerBellForJoining($storeId, $userId, $actionType);

		// add the user to the store's region
		$regionId = $this->storeGateway->getStoreRegionId($storeId);
		$this->regionGateway->linkBezirk($userId, $regionId);
	}

	/**
	 * Rejects (denies) a user's request for a store and creates a bell notification for that user.
	 */
	public function declineStoreRequest(int $storeId, int $userId): void
	{
		$this->storeGateway->removeUserFromTeam($storeId, $userId);

		// userId = affected user, sessionId = active user
		// => don't add a bell notification if the request was withdrawn by the user
		if ($userId !== $this->session->id()) {
			$this->triggerBellForJoining($storeId, $userId, StoreLogAction::REQUEST_DECLINED);
		}
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

	public function addStoreMember(int $storeId, int $userId, bool $moveToStandby = false): void
	{
		$this->addUserToStore($storeId, $userId, $moveToStandby);

		$this->storeGateway->addStoreLog($storeId, $this->session->id(), $userId, null, StoreLogAction::ADDED_WITHOUT_REQUEST);

		$this->triggerBellForJoining($storeId, $userId, StoreLogAction::ADDED_WITHOUT_REQUEST);
	}

	public function removeStoreMember(int $storeId, int $userId): void
	{
		$this->pickupGateway->deleteAllDatesFromAFoodsaver($userId, $storeId);
		$this->storeGateway->removeUserFromTeam($storeId, $userId);

		$this->storeGateway->addStoreLog($storeId, $this->session->id(), $userId, null, StoreLogAction::REMOVED_FROM_STORE);

		if ($teamChatConversationId = $this->storeGateway->getBetriebConversation($storeId)) {
			$this->messageGateway->deleteUserFromConversation($teamChatConversationId, $userId);
		}

		if ($jumperChatConversationId = $this->storeGateway->getBetriebConversation($storeId, true)) {
			$this->messageGateway->deleteUserFromConversation($jumperChatConversationId, $userId);
		}
	}

	public function leaveAllStoreTeams(int $userId): void
	{
		$ownStoreIds = $this->storeGateway->listStoreIds($userId);

		foreach ($ownStoreIds as $storeId) {
			$this->removeStoreMember($storeId, $userId);
		}
	}

	public function moveMemberToStandbyTeam(int $storeId, int $userId): void
	{
		$this->storeGateway->setUserMembershipStatus($storeId, $userId, MembershipStatus::JUMPER);

		$standbyTeamChatId = $this->storeGateway->getBetriebConversation($storeId, true);
		if ($standbyTeamChatId) {
			$this->messageGateway->addUserToConversation($standbyTeamChatId, $userId);
		}

		$teamChatId = $this->storeGateway->getBetriebConversation($storeId);
		if ($teamChatId) {
			$this->messageGateway->deleteUserFromConversation($teamChatId, $userId);
		}

		$this->storeGateway->addStoreLog($storeId, $this->session->id(), $userId, null, StoreLogAction::MOVED_TO_JUMPER);
	}

	public function moveMemberToRegularTeam(int $storeId, int $userId): void
	{
		$this->storeGateway->setUserMembershipStatus($storeId, $userId, MembershipStatus::MEMBER);

		$teamChatId = $this->storeGateway->getBetriebConversation($storeId);
		if ($teamChatId) {
			$this->messageGateway->addUserToConversation($teamChatId, $userId);
		}

		$standbyTeamChatId = $this->storeGateway->getBetriebConversation($storeId, true);
		if ($standbyTeamChatId) {
			$this->messageGateway->deleteUserFromConversation($standbyTeamChatId, $userId);
		}

		$this->storeGateway->addStoreLog($storeId, $this->session->id(), $userId, null, StoreLogAction::MOVED_TO_TEAM);
	}

	public function makeMemberResponsible(int $storeId, int $userId): void
	{
		$this->storeGateway->addStoreManager($storeId, $userId);
		$this->storeGateway->addStoreLog($storeId, $this->session->id(), $userId, null, StoreLogAction::APPOINT_STORE_MANAGER);

		$standbyTeamChatId = $this->storeGateway->getBetriebConversation($storeId, true);
		if ($standbyTeamChatId) {
			$this->messageGateway->addUserToConversation($standbyTeamChatId, $userId);
		}
	}

	public function downgradeResponsibleMember(int $storeId, int $userId): void
	{
		/* check if other managers exist (cannot leave as last manager) */
		$this->storeGateway->removeStoreManager($storeId, $userId);

		$standbyTeamChatId = $this->storeGateway->getBetriebConversation($storeId, true);
		if ($standbyTeamChatId) {
			$this->messageGateway->deleteUserFromConversation($standbyTeamChatId, $userId);
		}
	}

	private function addUserToStore(int $storeId, int $userId, bool $moveToStandby): void
	{
		$this->storeGateway->addUserToTeam($storeId, $userId);

		if ($moveToStandby) {
			$this->moveMemberToStandbyTeam($storeId, $userId);
		} else {
			$this->moveMemberToRegularTeam($storeId, $userId);
		}

		$this->storeGateway->add_betrieb_notiz([
			'foodsaver_id' => $userId,
			'betrieb_id' => $storeId,
			'text' => '{ACCEPT_REQUEST}',
			'zeit' => date('Y-m-d H:i:s'),
			'milestone' => Milestone::ACCEPTED,
		]);
	}

	/**
	 * creates an empty team conversation for the given store.
	 * creates an empty standby-team conversation for the given store.
	 * prefills both conversations with the given userId.
	 */
	private function createTeamConversations(int $storeId, int $managerId): void
	{
		$storeTeamChatId = $this->messageGateway->createConversation([$managerId], true);
		$this->storeGateway->updateStoreConversation($storeId, $storeTeamChatId, false);

		$standbyTeamChatId = $this->messageGateway->createConversation([$managerId], true);
		$this->storeGateway->updateStoreConversation($storeId, $standbyTeamChatId, true);
	}

	// notify people who can do something with the request: store managers, region ambassadors, or orga
	private function notifyStoreManagersAboutRequest(int $storeId, int $userId): void
	{
		$bellRecipients = $this->storeGateway->getBiebsForStore($storeId);
		if (!$bellRecipients) {
			$regionId = $this->storeGateway->getStoreRegionId($storeId);
			$ambassadors = $this->foodsaverGateway->getAdminsOrAmbassadors($regionId);

			if ($ambassadors) {
				$bellRecipients = array_column($ambassadors, 'id');
			} else {
				$bellRecipients = $this->foodsaverGateway->getOrgaTeam();
			}
		}

		$storeName = $this->storeGateway->getStoreName($storeId);

		$bellData = Bell::create('store_new_request_title', 'store_new_request', 'fas fa-user-plus', [
			'href' => '/?page=fsbetrieb&id=' . $storeId,
		], [
			'user' => $this->session->user('name'),
			'name' => $storeName,
		], 'store-request-' . $storeId);

		$this->bellGateway->addBell($bellRecipients, $bellData);
	}

	private function triggerBellForJoining(int $storeId, int $userId, int $actionType): void
	{
		if ($actionType === StoreLogAction::ADDED_WITHOUT_REQUEST) {
			$bellTitle = 'store_request_imposed_title';
			$bellMsg = 'store_request_imposed';
			$bellIcon = 'fas fa-user-plus';
			$bellId = 'store-imposed-' . $storeId . '-' . $userId;
		} elseif ($actionType === StoreLogAction::MOVED_TO_JUMPER) {
			$bellTitle = 'store_request_accept_wait_title';
			$bellMsg = 'store_request_accept_wait';
			$bellIcon = 'fas fa-user-tag';
			$bellId = BellType::createIdentifier(BellType::STORE_REQUEST_WAITING, $userId);
		} elseif ($actionType === StoreLogAction::REQUEST_APPROVED) {
			$bellTitle = 'store_request_accept_title';
			$bellMsg = 'store_request_accept';
			$bellIcon = 'fas fa-user-check';
			$bellId = BellType::createIdentifier(BellType::STORE_REQUEST_ACCEPTED, $userId);
		} elseif ($actionType === StoreLogAction::REQUEST_DECLINED) {
			$bellTitle = 'store_request_deny_title';
			$bellMsg = 'store_request_deny';
			$bellIcon = 'fas fa-user-times';
			$bellId = BellType::createIdentifier(BellType::STORE_REQUEST_REJECTED, $userId);
		} else {
			throw new \DomainException('Unknown store-team action: ' . $actionType);
		}
		$bellLink = '/?page=fsbetrieb&id=' . $storeId;

		$storeName = $this->storeGateway->getStoreName($storeId);

		$bellData = Bell::create($bellTitle, $bellMsg, $bellIcon, [
			'href' => $bellLink,
		], [
			'user' => $this->session->user('name'),
			'name' => $storeName,
		], $bellId);
		$this->bellGateway->addBell($userId, $bellData);
	}
}
