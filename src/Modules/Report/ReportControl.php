<?php

namespace Foodsharing\Modules\Report;

use Foodsharing\Modules\Core\Control;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Foodsharing\Services\ImageService;

class ReportControl extends Control
{
	private $reportGateway;
	private $imageService;

	public function __construct(ReportGateway $reportGateway, ReportView $view, ImageService $imageService)
	{
		$this->reportGateway = $reportGateway;
		$this->view = $view;
		$this->imageService = $imageService;

		parent::__construct();
	}

	// Request is needed here, even if not used inside the method.
	public function index(Request $request, Response $response): void
	{
		if (isset($_GET['bid'])) {
			$this->byRegion($_GET['bid'], $response);
		} else {
			if (!isset($_GET['sub'])) {
				$this->routeHelper->go('/?page=report&sub=uncom');
			}
			if ($this->session->mayHandleReports()) {
				$this->pageHelper->addBread('Meldungen', '/?page=report');
			} else {
				$this->routeHelper->go('/?page=dashboard');
			}
		}
	}

	private function byRegion($regionId, $response)
	{
		$response->setContent($this->render('pages/Report/by-region.twig',
			['bid' => $regionId]
		));
	}

	public function uncom(): void
	{
		if ($this->session->mayHandleReports()) {
			$this->pageHelper->addContent($this->view->statsMenu($this->reportGateway->getReportStats()), CNT_LEFT);

			if ($reports = $this->reportGateway->getReports(0)) {
				$this->pageHelper->addContent($this->view->listReports($reports));
			}
			$this->pageHelper->addContent($this->view->topbar('Neue Meldungen', \count($reports) . ' insgesamt', '<img src="/img/shit.png" />'), CNT_TOP);
		}
	}

	public function com(): void
	{
		if ($this->session->mayHandleReports()) {
			$this->pageHelper->addContent($this->view->statsMenu($this->reportGateway->getReportStats()), CNT_LEFT);

			if ($reports = $this->reportGateway->getReports(1)) {
				$this->pageHelper->addContent($this->view->listReports($reports));
			}
			$this->pageHelper->addContent($this->view->topbar('Zugestellte Meldungen', \count($reports) . ' insgesamt', '<img src="/img/shit.png" />'), CNT_TOP);
		}
	}

	public function foodsaver(): void
	{
		if ($this->session->mayHandleReports()) {
			if ($foodsaver = $this->reportGateway->getReportedSaver($_GET['id'])) {
				$this->pageHelper->addBread(
					'Meldungen',
					'/?page=report&sub=foodsaver&id=' . (int)$foodsaver['id']
				);
				$this->pageHelper->addJs(
					'
						$(".welcome_profile_image").css("cursor","pointer");
						$(".welcome_profile_image").on("click", function(){
							$(".user_display_name a").trigger("click");
						});
				'
				);
				$this->pageHelper->addContent(
					$this->view->topbar(
						'Meldungen von <a href="/profile/' . (int)$foodsaver['id'] . '">' . $foodsaver['name'] . ' ' . $foodsaver['nachname'] . '</a>',
						\count($foodsaver['reports']) . ' gesamt',
						$this->imageService->avatar($foodsaver, 50)
					),
					CNT_TOP
				);
				$this->pageHelper->addContent(
					$this->v_utils->v_field(
						$this->wallposts('fsreport', (int)$_GET['id']),
						'Notizen und Entscheidungen'
					)
				);
				$this->pageHelper->addContent(
					$this->view->listReportsTiny($foodsaver['reports']),
					CNT_RIGHT
				);
			}
		} else {
			$this->routeHelper->go('/?page=dashboard');
		}
	}
}
