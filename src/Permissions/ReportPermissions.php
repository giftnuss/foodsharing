<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;

class ReportPermissions
{
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function mayAccessReportsForRegion($regionId): bool
	{
		/* from https://gitlab.com/foodsharing-dev/foodsharing/issues/296
		 * reports list on region level is accessible for orga and for the AMBs of that very region
		 * The reports team is having access to the Europe-Region list.
		 *
		 */
		return
			$this->session->isOrgaTeam() ||
			$this->session->isAdminFor($regionId) ||
			/* ToDo: Need to check that regionId is a subgroup of europe. implied for now. */
			$this->session->mayGroup(RegionIDs::EUROPE_REPORT_TEAM)
		;
	}
}
