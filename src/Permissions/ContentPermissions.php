<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;


final class ContentPermissions
{
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function mayEditContent()
	{
        return $this->session->may('orga')
			|| $this->session->isAdminFor(RegionIDs::PR_PARTNER_AND_TEAM_WORK_GROUP
			|| $this->session->isAdminFor(RegionIDs::PR_START_PAGE));
	}
}
