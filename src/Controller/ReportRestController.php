<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Report\ReportGateway;
use Foodsharing\Permissions\ReportPermissions;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
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
	 * @param $regionId integer for which region the reports should be returned
	 * @Rest\Get("report/region/{regionId}", requirements={"regionId" = "\d+"})
	 * @return \Symfony\Component\HttpFoundation\Response
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
		if ($this->reportPermissions->mayAccessReportsForSubRegions()) {
			$regions = $this->regionGateway->listIdsForDescendantsAndSelf($regionId);
		} else {
			$regions = [$regionId];
		}

		$reports = array_merge(
			$this->reportGateway->getReportsByReporteeRegions($regions, $this->session->id()),
			$this->reportGateway->getReportsForRegionlessByReporterRegion($regions, $this->session->id())
		);

		$view = $this->view([
			'data' => $reports
		], 200);

		return $this->handleView($view);
	}
}
