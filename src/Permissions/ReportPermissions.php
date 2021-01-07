<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Region\RegionGateway;

class ReportPermissions
{
	private Session $session;
	private RegionGateway $regionGateway;

	public function __construct(
		Session $session,
		RegionGateway $regionGateway)
	{
		$this->session = $session;
		$this->regionGateway = $regionGateway;
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

		$reportGroup = $this->regionGateway->getRegionFunctionGroupId($regionId, WorkgroupFunction::REPORT);

		if (!empty($reportGroup)) {
			if ($this->session->isAdminFor($reportGroup)) {
				return true;
			}
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
