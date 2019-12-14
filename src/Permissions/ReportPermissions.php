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

	public function mayAccessReportsForRegion(int $regionId): bool
	{
		/* from https://gitlab.com/foodsharing-dev/foodsharing/issues/296
		 * and https://gitlab.com/foodsharing-dev/foodsharing/merge_requests/529
		 * reports list on region level is accessible for orga and for the AMBs of that exact region
		 * The reports team is having access to the Europe-Region list.
		 *
		 */
		return
			$this->session->isAdminFor($regionId) ||
			/* ToDo: Need to check that regionId is a subgroup of europe. implied for now. */
			$this->session->isOrgaTeam() ||
			$this->session->isAdminFor(RegionIDs::EUROPE_REPORT_TEAM)
		;
	}

	public function mayAccessReportsForSubRegions(): bool
	{
		return $this->session->isAdminFor(RegionIDs::EUROPE_REPORT_TEAM);
	}

	public function mayHandleReports()
	{
		// group "Regelverletzungen/Meldungen"
		return $this->session->may('orga') || $this->session->isAdminFor(RegionIDs::EUROPE_REPORT_TEAM);
	}
}
