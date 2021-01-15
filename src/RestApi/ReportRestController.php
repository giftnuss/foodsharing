<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;
use Foodsharing\Modules\Group\GroupFunctionGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Report\ReportGateway;
use Foodsharing\Permissions\ReportPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReportRestController extends AbstractFOSRestController
{
	private Session $session;
	private RegionGateway $regionGateway;
	private ReportGateway $reportGateway;
	private ReportPermissions $reportPermissions;
	private GroupFunctionGateway $groupFunctionGateway;

	public function __construct(
		Session $session,
		RegionGateway $regionGateway,
		ReportGateway $reportGateway,
		ReportPermissions $reportPermissions,
		GroupFunctionGateway $groupFunctionGateway
	) {
		$this->session = $session;
		$this->regionGateway = $regionGateway;
		$this->reportGateway = $reportGateway;
		$this->reportPermissions = $reportPermissions;
		$this->groupFunctionGateway = $groupFunctionGateway;
	}

	/**
	 * @param int $regionId for which region the reports should be returned
	 * @Rest\Get("report/region/{regionId}", requirements={"regionId" = "\d+"})
	 */
	public function listReportsForRegionAction(int $regionId): Response
	{
		if (!$this->reportPermissions->mayAccessReportsForRegion($regionId)) {
			throw new HttpException(403);
		}

		/* from https://gitlab.com/foodsharing-dev/foodsharing/issues/296 with
		  https://gitlab.com/foodsharing-dev/foodsharing/merge_requests/529
		  reports lists do show every report from that region excluding the child regions
		  reports lists do only show the reports of the visitor if anonymity has been repealed by the reporter (feature yet to come)
		  -> remove reports of the person visiting from output
		*/

		if ($this->reportPermissions->mayAccessReportsForSubRegions()) {
			$regions = $this->regionGateway->listIdsForDescendantsAndSelf($regionId);
		// this path implicitly includes reports against ambassadors for subregions as it includes all of them anyway.
		} else {
			$regions = [$regionId];
			/* this path needs to add reports against ambassadors of subregions because they will not see themselves. Exclude $regionId
			so no report is shown twice. */

			$addReportsAgainstAmbassadorsForRegions = $this->regionGateway->listIdsForDescendantsAndSelf($regionId, false);
		}

		$excludeIDs = null;
		$reportGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($regionId, WorkgroupFunction::REPORT);
		$onlyWithIds = null;
		$reportAdminIDs = null;
		if (!empty($reportGroup)) {
			$reportAdminIDs = $this->regionGateway->getFsAdminIdsFromRegion($reportGroup);
			if (in_array($this->session->id(), $reportAdminIDs)) {
				$excludeIDs = $reportAdminIDs;
			}
		}
		$arbitrationAdminIDs = null;
		$arbitrationGroup = $this->groupFunctionGateway->getRegionFunctionGroupId($regionId, WorkgroupFunction::ARBITRATION);
		if (!empty($arbitrationGroup)) {
			$arbitrationAdminIDs = $this->regionGateway->getFsAdminIdsFromRegion($arbitrationGroup);
			if (in_array($this->session->id(), $arbitrationAdminIDs)) {
				$excludeIDs = $arbitrationAdminIDs;
				if (!empty($reportAdminIDs)) {
					$onlyWithIds = $reportAdminIDs;
				}
			}
		}

		if (!empty($reportGroup) &&
			!empty($arbitrationGroup)) {
			if (in_array($this->session->id(), $arbitrationAdminIDs) &&
				in_array($this->session->id(), $reportAdminIDs)) {
				throw new HttpException(403);
			}
		}

		$reports = $this->reportGateway->getReportsByReporteeRegions($regions, $excludeIDs, $onlyWithIds);

		return $this->handleView($this->view(['data' => $reports], 200));
	}
}
