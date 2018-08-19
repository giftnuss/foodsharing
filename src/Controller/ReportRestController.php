<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Report\ReportGateway;
use Foodsharing\Permissions\ReportPermissions;
use Foodsharing\Services\SanitizerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReportRestController extends FOSRestController
{
	private $session;
	private $sanitizerService;
	private $reportPermissions;
	private $reportGateway;
	private $regionGateway;

	public function __construct(Session $session, SanitizerService $sanitizerService, RegionGateway $regionGateway, ReportGateway $reportGateway, ReportPermissions $reportPermissions)
	{
		$this->session = $session;
		$this->sanitizerService = $sanitizerService;
		$this->regionGateway = $regionGateway;
		$this->reportGateway = $reportGateway;
		$this->reportPermissions = $reportPermissions;
	}

	/**
	 * @param $regionId integer for which region the reports should be returned
	 * @Rest\Get("report/region/{regionId}", requirements={"regionId" = "\d+"})
	 */
	public function listReportsForRegionAction(int $regionId)
	{
		if (!$this->reportPermissions->mayAccessReportsForRegion($regionId)) {
			throw new HttpException(403);
		}

		/* from https://gitlab.com/foodsharing-dev/foodsharing/issues/296
		  reports lists do show every report from that region and all child regions (filter will be needed)
		  reports lists do only show the reports of the visitor if anonymity has been repealed by the reporter (feature yet to come)
		  -> remove reports of the person visiting from output
		*/
		$regions = $this->regionGateway->listIdsForDescendantsAndSelf($regionId);

		$reports = $this->reportGateway->getReports(null, $regions, $this->session->id());

		$view = $this->view([
			'data' => $reports
		], 200);

		return $this->handleView($view);
	}
}
