<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Store\StoreGateway;

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

	public function mayEditPickups($storeId)
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
}
