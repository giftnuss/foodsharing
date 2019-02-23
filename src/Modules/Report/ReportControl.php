<?php

namespace Foodsharing\Modules\Report;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Services\ImageService;

class ReportControl extends Control
{
	private $reportGateway;
	private $imageService;

	public function __construct(ReportGateway $reportGateway, Db $model, ReportView $view, ImageService $imageService)
	{
		$this->reportGateway = $reportGateway;
		$this->model = $model;
		$this->view = $view;
		$this->imageService = $imageService;

		parent::__construct();

		if (!isset($_GET['sub'])) {
			$this->routeHelper->go('/?page=report&sub=uncom');
		}
	}

	public function index(): void
	{
		if ($this->session->mayHandleReports()) {
			$this->pageHelper->addBread('Meldungen', '/?page=report');
		} else {
			$this->routeHelper->go('/?page=dashboard');
		}
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
			$this->pageHelper->addContent($this->view->topbar('Bestätigte Meldungen', \count($reports) . ' insgesamt', '<img src="/img/shit.png" />'), CNT_TOP);
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
