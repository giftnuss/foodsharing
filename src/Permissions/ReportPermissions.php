<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;

class ReportPermissions
{
	private Session $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	/** Reports list on region level: accessible for orga and for the AMBs of that exact region
	 * The reports team has access to the Europe-Region list.
	 *
	 * from https://gitlab.com/foodsharing-dev/foodsharing/issues/296
	 * and https://gitlab.com/foodsharing-dev/foodsharing/merge_requests/529
	 */
	public function mayAccessReportsForRegion(int $regionId): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}
		if ($this->session->isAdminFor($regionId)) {
			return true;
		}

		// ToDo: Need to check that regionId is a subgroup of europe. implied for now.
		return $this->session->isAdminFor(RegionIDs::EUROPE_REPORT_TEAM);
	}

	public function mayAccessReportsForSubRegions(): bool
	{
		return $this->session->isAdminFor(RegionIDs::EUROPE_REPORT_TEAM);
	}

	public function mayHandleReports(): bool
	{
		// group "Regelverletzungen/Meldungen"
		return $this->session->may('orga') || $this->session->isAdminFor(RegionIDs::EUROPE_REPORT_TEAM);
	}
}
