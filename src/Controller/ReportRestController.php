<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Report\ReportGateway;
use Foodsharing\Permissions\ReportPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReportRestController extends AbstractFOSRestController
{
	private $session;
	private $reportPermissions;
	private $reportGateway;
	private $regionGateway;

	public function __construct(Session $session, RegionGateway $regionGateway, ReportGateway $reportGateway, ReportPermissions $reportPermissions)
	{
		$this->session = $session;
		$this->regionGateway = $regionGateway;
		$this->reportGateway = $reportGateway;
		$this->reportPermissions = $reportPermissions;
	}

	/**
	 * @param int $regionId for which region the reports should be returned
	 * @Rest\Get("report/region/{regionId}", requirements={"regionId" = "\d+"})
	 */
	public function listReportsForRegionAction(int $regionId): \Symfony\Component\HttpFoundation\Response
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

		$addReportsAgainstAmbassadorsForRegions = [];
		if ($this->reportPermissions->mayAccessReportsForSubRegions()) {
			$regions = $this->regionGateway->listIdsForDescendantsAndSelf($regionId);
		// this path implicitly includes reports against ambassadors for subregions as it includes all of them anyway.
		} else {
			$regions = [$regionId];
			/* this path needs to add reports against ambassadors of subregions because they will not see themselves. Exclude $regionId
			so no report is shown twice. */

			$addReportsAgainstAmbassadorsForRegions = $this->regionGateway->listIdsForDescendantsAndSelf($regionId, false);
		}

		$reports = array_merge(
			$this->reportGateway->getReportsByReporteeRegions($regions, $this->session->id()),
			$this->reportGateway->getReportsForRegionlessByReporterRegion($regions, $this->session->id()),
			$this->reportGateway->getReportsByReporteeRegions($addReportsAgainstAmbassadorsForRegions, $this->session->id(), true)
		);

		$view = $this->view([
			'data' => $reports
		], 200);

		return $this->handleView($view);
	}
}
