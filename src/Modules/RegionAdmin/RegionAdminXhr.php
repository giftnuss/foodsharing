<?php

namespace Foodsharing\Modules\RegionAdmin;

use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;

class RegionAdminXhr extends Control
{
	private $regionGateway;

	public function __construct(RegionGateway $regionGateway)
	{
		$this->regionGateway = $regionGateway;

		parent::__construct();
	}

	public function masterupdate()
	{
		if (!$this->session->may('orga')) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if ($bezirke = $this->regionGateway->listIdsForDescendantsAndSelf($_GET['id'])) {
			$this->regionGateway->updateMasterRegions($bezirke, $_GET['id']);
		}
	}
}
