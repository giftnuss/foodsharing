<?php

namespace Foodsharing\Permissions;

use Carbon\Carbon;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Store\TeamStatus as StoreTeamStatus;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\TeamStatus as UserTeamStatus;

class StorePermissions
{
	private StoreGateway $storeGateway;
	private Session $session;

	public function __construct(
		StoreGateway $storeGateway,
		Session $session
	) {
		$this->storeGateway = $storeGateway;
		$this->session = $session;
	}

	public function mayJoinStoreRequest(int $storeId): bool
	{
		$fsId = $this->session->id();
		if (!$fsId) {
			return false;
		}

		$storeTeamStatus = $this->storeGateway->getStoreTeamStatus($storeId);

		// store open?
		if (!in_array($storeTeamStatus, [StoreTeamStatus::OPEN, StoreTeamStatus::OPEN_SEARCHING])) {
			return false;
		}

		// already in team?
		if ($this->storeGateway->getUserTeamStatus($fsId, $storeId) !== UserTeamStatus::NoMember) {
			return false;
		}

		return true;
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
		if ($this->session->isAdminFor($storeRegion)) {
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
		if ($this->session->isAdminFor($storeRegion)) {
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
		if ($this->session->isAdminFor($storeRegion)) {
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
		return (!$store['jumper'] || $store['verantwortlich'])
			&& $store['team_conversation_id'] !== null;
	}

	public function mayChatWithJumperWaitingTeam(array $store): bool
	{
		return $store['verantwortlich'] && $store['springer_conversation_id'] !== null;
	}
}
