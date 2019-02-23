<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Core\DBConstants\Store\TeamStatus;

class StorePermissions
{
	private $storeGateway;
	private $session;

	public function __construct(
		StoreGateway $storeGateway,
		Session $session
	) {
		$this->storeGateway = $storeGateway;
		$this->session = $session;
	}

	public function mayJoinStoreRequest($storeId)
	{
		$fsId = $this->session->id();
		if (!$fsId) {
			return false;
		}

		$store = $this->storeGateway->getBetrieb($storeId);

		// store open?
		if (!in_array($store['team_status'], [TeamStatus::OPEN, TeamStatus::OPEN_SEARCHING])) {
			return false;
		}

		// already in team?
		if ($this->storeGateway->isInTeam($fsId, $storeId)) {
			return false;
		}

		return true;
	}

	public function mayAccessStore($storeId)
	{
		$fsId = $this->session->id();
		if (!$fsId) {
			return false;
		}

		if ($this->session->isOrgaTeam()) {
			return true;
		}
		if ($this->storeGateway->isInTeam($fsId, $storeId)) {
			return true;
		}

		$store = $this->storeGateway->getBetrieb($storeId);
		if ($this->session->isAdminFor($store['bezirk_id'])) {
			return true;
		}

		return false;
	}

	public function mayEditStore($storeId)
	{
		$fsId = $this->session->id();
		if (!$fsId) {
			return false;
		}

		if ($this->session->isOrgaTeam()) {
			return true;
		}
		if ($this->storeGateway->isResponsible($fsId, $storeId)) {
			return true;
		}
		$store = $this->storeGateway->getBetrieb($storeId);
		if ($this->session->isAdminFor($store['bezirk_id'])) {
			return true;
		}

		return false;
	}

	public function mayEditPickups($storeId)
	{
		return $this->mayEditStore($storeId);
	}

	public function mayAcceptRequests($storeId)
	{
		return $this->mayEditStore($storeId);
	}

	public function mayAddPickup($storeId)
	{
		return $this->mayEditPickups($storeId);
	}

	public function mayDeletePickup($storeId)
	{
		return $this->mayEditPickups($storeId);
	}

	public function maySeeFetchHistory($storeId)
	{
		return $this->mayEditStore($storeId);
	}

	public function mayDoPickup($storeId)
	{
		if (!$this->session->isVerified()) {
			return false;
		}

		if (!$this->mayAccessStore($storeId)) {
			return false;
		}

		return true;
	}

	public function hasPreconfirmedPickup($storeId)
	{
		$fsId = $this->session->id();
		if (!$fsId) {
			return false;
		}
		if ($this->session->isOrgaTeam() || $this->storeGateway->isResponsible($fsId, $storeId)) {
			return true;
		}

		return false;
	}
}
