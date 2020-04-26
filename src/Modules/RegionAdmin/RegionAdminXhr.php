<?php

namespace Foodsharing\Modules\RegionAdmin;

use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\RegionPermissions;

class RegionAdminXhr extends Control
{
	private $regionGateway;
	private $regionPermissions;

	public function __construct(RegionGateway $regionGateway, RegionPermissions $regionPermissions)
	{
		$this->regionGateway = $regionGateway;
		$this->regionPermissions = $regionPermissions;

		parent::__construct();
	}

	public function masterupdate()
	{
		if (!$this->regionPermissions->mayAdministrateRegions()) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if ($bezirke = $this->regionGateway->listIdsForDescendantsAndSelf($_GET['id'])) {
			$this->regionGateway->updateMasterRegions($bezirke, $_GET['id']);
		}
	}
}
