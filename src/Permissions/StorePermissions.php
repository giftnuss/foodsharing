<?php

namespace Foodsharing\Permissions;

use Carbon\Carbon;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Core\DBConstants\Store\TeamStatus as StoreTeamStatus;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\TeamStatus as UserTeamStatus;

class StorePermissions
{
	private StoreGateway $storeGateway;
	private Session $session;
	private RegionGateway $regionGateway;
	private GroupFunctionGateway $groupFunctionGateway;

	public function __construct(
		StoreGateway $storeGateway,
		Session $session,
		RegionGateway $regionGateway,
		GroupFunctionGateway $groupFunctionGateway
	) {
		$this->storeGateway = $storeGateway;
		$this->session = $session;
		$this->regionGateway = $regionGateway;
		$this->groupFunctionGateway = $groupFunctionGateway;
	}

	/**
	 * Assumes that the given user is a foodsaver (i.e. can join store teams).
	 * Just the additional permissions for the given, specific store are checked.
	 */
	public function mayJoinStoreRequest(int $storeId, ?int $userId = null): bool
	{
		$userId ??= $this->session->id();
		if (is_null($userId)) {
			return false;
		}

		$storeTeamStatus = $this->storeGateway->getStoreTeamStatus($storeId);

		// store open?
		if (!in_array($storeTeamStatus, [StoreTeamStatus::OPEN, StoreTeamStatus::OPEN_SEARCHING])) {
			return false;
		}

		// already in team?
		if ($this->storeGateway->getUserTeamStatus($userId, $storeId) !== UserTeamStatus::NoMember) {
			return false;
		}

		return true;
	}

	public function mayAddUserToStoreTeam(int $storeId, int $userId, int $userRole): bool
	{
		if (!$this->mayEditStoreTeam($storeId)) {
			return false;
		}
		if ($this->storeGateway->getUserTeamStatus($userId, $storeId) !== UserTeamStatus::NoMember) {
			return false;
		}

		return $userRole >= Role::FOODSAVER;
	}

	/**
	 * Does not check if the given user is part of the store team.
	 * If that is not guaranteed, you will need to verify membership yourself.
	 */
	public function mayLeaveStoreTeam(int $storeId, int $userId): bool
	{
		$currentManagers = $this->storeGateway->getStoreManagers($storeId);
		$isManager = in_array($userId, $currentManagers, true);

		return !$isManager;
	}

	public function mayAccessStore(int $storeId): bool
	{
		$fsId = $this->session->id();
		if (!$fsId) {
			return false;
		}

		if ($this->session->may('orga')) {
			return true;
		}
		if ($this->storeGateway->getUserTeamStatus($fsId, $storeId) >= UserTeamStatus::WaitingList) {
			return true;
		}

		$storeRegion = $this->storeGateway->getStoreRegionId($storeId);
		$storeGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($storeRegion, WorkgroupFunction::STORES_COORDINATION);
		if (empty($storeGroup)) {
			if ($this->session->isAdminFor($storeRegion)) {
				return true;
			}
		} elseif ($this->session->isAdminFor($storeGroup)) {
			return true;
		}

		return false;
	}

	public function mayReadStoreWall(int $storeId): bool
	{
		$fsId = $this->session->id();
		if (!$fsId) {
			return false;
		}

		if ($this->session->may('orga')) {
			return true;
		}
		if ($this->storeGateway->getUserTeamStatus($fsId, $storeId) >= UserTeamStatus::Member) {
			return true;
		}

		$storeRegion = $this->storeGateway->getStoreRegionId($storeId);
		$storeGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($storeRegion, WorkgroupFunction::STORES_COORDINATION);
		if (empty($storeGroup)) {
			if ($this->session->isAdminFor($storeRegion)) {
				return true;
			}
		} elseif ($this->session->isAdminFor($storeGroup)) {
			return true;
		}

		return false;
	}

	public function mayWriteStoreWall(int $storeId): bool
	{
		return $this->mayReadStoreWall($storeId);
	}

	/**
	 * Can remove any store wallpost, regardless of author and creation time.
	 */
	public function mayDeleteStoreWall(int $storeId): bool
	{
		return $this->session->may('orga');
	}

	/**
	 * Can remove this specific store wallpost right now.
	 */
	public function mayDeleteStoreWallPost(int $storeId, int $postId): bool
	{
		if (!$this->session->may()) {
			return false;
		}
		if ($this->mayDeleteStoreWall($storeId)) {
			return true;
		}

		$post = $this->storeGateway->getStoreWallpost($storeId, $postId);

		if (!$post) {
			return false;
		}
		if ($this->session->id() === $post['foodsaver_id']) {
			return true;
		}
		if ($this->mayEditStore($storeId)) {
			return $post['zeit'] <= Carbon::today()->subMonth();
		}

		return false;
	}

	public function mayCreateStore(): bool
	{
		return $this->session->may('bieb');
	}

	public function mayEditStore(int $storeId): bool
	{
		$fsId = $this->session->id();
		if (!$fsId) {
			return false;
		}

		if (!$this->session->may('bieb')) {
			return false;
		}

		if ($this->session->may('orga')) {
			return true;
		}
		if ($this->storeGateway->getUserTeamStatus($fsId, $storeId) === UserTeamStatus::Coordinator) {
			return true;
		}
		$storeRegion = $this->storeGateway->getStoreRegionId($storeId);
		$storeGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($storeRegion, WorkgroupFunction::STORES_COORDINATION);
		if (empty($storeGroup)) {
			if ($this->session->isAdminFor($storeRegion)) {
				return true;
			}
		} elseif ($this->session->isAdminFor($storeGroup)) {
			return true;
		}

		return false;
	}

	public function mayEditStoreTeam(int $storeId): bool
	{
		return $this->mayEditStore($storeId);
	}

	public function mayRemovePickupUser(int $storeId, int $fsId): bool
	{
		if ($fsId === $this->session->id()) {
			return true;
		}

		if ($this->mayEditPickups($storeId)) {
			return true;
		}

		return false;
	}

	public function mayConfirmPickup(int $storeId): bool
	{
		return $this->mayEditPickups($storeId);
	}

	public function mayEditPickups(int $storeId): bool
	{
		return $this->mayEditStore($storeId);
	}

	public function mayAcceptRequests(int $storeId): bool
	{
		return $this->mayEditStore($storeId);
	}

	public function mayAddPickup(int $storeId): bool
	{
		return $this->mayEditPickups($storeId);
	}

	public function mayDeletePickup(int $storeId): bool
	{
		return $this->mayEditPickups($storeId);
	}

	public function maySeePickupHistory(int $storeId): bool
	{
		return $this->mayEditStore($storeId);
	}

	public function mayDoPickup(int $storeId): bool
	{
		if (!$this->session->isVerified()) {
			return false;
		}

		if (!$this->mayReadStoreWall($storeId)) {
			return false;
		}

		return true;
	}

	public function maySeePickups(int $storeId): bool
	{
		return $this->mayDoPickup($storeId);
	}

	public function maySeePhoneNumbers(int $storeId): bool
	{
		return $this->mayDoPickup($storeId);
	}

	public function mayChatWithRegularTeam(array $store): bool
	{
		if ($store['jumper']) {
			return false;
		}

		return $store['team_conversation_id'] !== null;
	}

	public function mayChatWithJumperWaitingTeam(array $store): bool
	{
		return ($store['verantwortlich'] || $store['jumper']) && $store['springer_conversation_id'] !== null;
	}

	/**
	 * This permission roughly assumes that both user and store exist.
	 * If that is not guaranteed, you will need to check existence in the callers!
	 */
	public function mayBecomeStoreManager(int $storeId, int $userId, int $userRole): bool
	{
		$currentManagers = $this->storeGateway->getStoreManagers($storeId);

		// at most three managers are allowed right now
		if (count($currentManagers) >= 3) {
			return false;
		}

		$isAlreadyManager = in_array($userId, $currentManagers, true);
		if ($isAlreadyManager) {
			return false;
		}

		return $userRole >= Role::STORE_MANAGER;
	}

	public function mayLoseStoreManagement(int $storeId, int $userId): bool
	{
		$currentManagers = $this->storeGateway->getStoreManagers($storeId);
		$isManager = in_array($userId, $currentManagers, true);
		if (!$isManager) {
			return false;
		}

		// at least one other manager needs to remain after leaving
		if (count($currentManagers) <= 1) {
			return false;
		}

		return true;
	}
}
