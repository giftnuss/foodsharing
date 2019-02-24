<?php

namespace Foodsharing\Modules\Report;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Control;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportControl extends Control
{
	private $reportGateway;

	public function __construct(ReportGateway $reportGateway, Db $model, ReportView $view)
	{
		$this->reportGateway = $reportGateway;
		$this->model = $model;
		$this->view = $view;

		parent::__construct();
	}

	public function index(Request $request, Response $response): void
	{
		if (isset($_GET['bid'])) {
			$this->byRegion($_GET['bid'], $response);
		} else {
			if (!isset($_GET['sub'])) {
				$this->func->go('/?page=report&sub=uncom');
			}
			if ($this->session->mayHandleReports()) {
				$this->pageCompositionHelper->addBread('Meldungen', '/?page=report');
			} else {
				$this->func->go('/?page=dashboard');
			}
		}
	}

	private function byRegion($bid, $response)
	{
		$response->setContent($this->render('pages/Report/by-region.twig',
			['bid' => $bid]
		));
	}

	public function uncom(): void
	{
		if ($this->session->mayHandleReports()) {
			$this->pageCompositionHelper->addContent($this->view->statsMenu($this->reportGateway->getReportStats()), CNT_LEFT);

			if ($reports = $this->reportGateway->getReports(0)) {
				$this->pageCompositionHelper->addContent($this->view->listReports($reports));
			}
			$this->pageCompositionHelper->addContent($this->view->topbar('Neue Meldungen', \count($reports) . ' insgesamt', '<img src="/img/shit.png" />'), CNT_TOP);
		}
	}

	public function com(): void
	{
		if ($this->session->mayHandleReports()) {
			$this->pageCompositionHelper->addContent($this->view->statsMenu($this->reportGateway->getReportStats()), CNT_LEFT);

			if ($reports = $this->reportGateway->getReports(1)) {
				$this->pageCompositionHelper->addContent($this->view->listReports($reports));
			}
			$this->pageCompositionHelper->addContent($this->view->topbar('Bestätigte Meldungen', \count($reports) . ' insgesamt', '<img src="/img/shit.png" />'), CNT_TOP);
		}
	}

	public function foodsaver(): void
	{
		if ($this->session->mayHandleReports()) {
			if ($foodsaver = $this->reportGateway->getReportedSaver($_GET['id'])) {
				$this->pageCompositionHelper->addBread('Meldungen', '/?page=report&sub=foodsaver&id=' . (int)$foodsaver['id']);
				$this->pageCompositionHelper->addJs('
						$(".welcome_profile_image").css("cursor","pointer");
						$(".welcome_profile_image").on("click", function(){
							$(".user_display_name a").trigger("click");
						});
				');
				$this->pageCompositionHelper->addContent($this->view->topbar('Meldungen von <a href="/profile/' . (int)$foodsaver['id'] . '">' . $foodsaver['name'] . ' ' . $foodsaver['nachname'] . '</a>', \count($foodsaver['reports']) . ' gesamt', $this->func->avatar($foodsaver, 50)), CNT_TOP);
				$this->pageCompositionHelper->addContent($this->v_utils->v_field($this->wallposts('fsreport', (int)$_GET['id']), 'Notizen und Entscheidungen'));
				$this->pageCompositionHelper->addContent($this->view->listReportsTiny($foodsaver['reports']), CNT_RIGHT);
			}
		} else {
			$this->func->go('/?page=dashboard');
		}
	}
}
