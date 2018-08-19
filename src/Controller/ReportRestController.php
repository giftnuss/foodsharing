<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
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

	public function __construct(Session $session, SanitizerService $sanitizerService, ReportGateway $reportGateway, ReportPermissions $reportPermissions)
	{
		$this->session = $session;
		$this->sanitizerService = $sanitizerService;
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

		$reports = $this->reportGateway->getReports(null, $regionId, $this->session->id());

		$view = $this->view([
			'data' => $reports
		], 200);

		return $this->handleView($view);
	}
}
