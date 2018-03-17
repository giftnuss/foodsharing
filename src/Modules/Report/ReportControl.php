<?php

namespace Foodsharing\Modules\Report;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;

class ReportControl extends Control
{
	private $gateway;

	public function __construct(ReportGateway $gateway, Model $model, ReportView $view)
	{
		$this->gateway = $gateway;
		$this->model = $model;
		$this->view = $view;

		parent::__construct();

		if (!isset($_GET['sub'])) {
			$this->func->go('/?page=report&sub=uncom');
		}
	}

	public function index(): void
	{
		if ($this->func->mayHandleReports()) {
			$this->func->addBread('Verstoßmeldungen', '/?page=report');
		} else {
			$this->func->go('/?page=dashboard');
		}
	}

	public function uncom(): void
	{
		if ($this->func->mayHandleReports()) {
			$this->func->addContent($this->view->statsMenu($this->gateway->getReportStats()), CNT_LEFT);

			if ($reports = $this->gateway->getReports(0)) {
				$this->func->addContent($this->view->listReports($reports));
			}
			$this->func->addContent($this->view->topbar('Neue Verstoßmeldungen', \count($reports) . ' insgesamt', '<img src="/img/shit.png" />'), CNT_TOP);
		}
	}

	public function com(): void
	{
		if ($this->func->mayHandleReports()) {
			$this->func->addContent($this->view->statsMenu($this->gateway->getReportStats()), CNT_LEFT);

			if ($reports = $this->gateway->getReports(1)) {
				$this->func->addContent($this->view->listReports($reports));
			}
			$this->func->addContent($this->view->topbar('Bestätigte Verstoßmeldungen', \count($reports) . ' insgesamt', '<img src="/img/shit.png" />'), CNT_TOP);
		}
	}

	public function foodsaver(): void
	{
		if ($this->func->mayHandleReports()) {
			if ($foodsaver = $this->gateway->getReportedSaver($_GET['id'])) {
				$this->func->addBread('Verstoßmeldungen', '/?page=report&sub=foodsaver&id=' . (int)$foodsaver['id']);
				$this->func->addJs('
						$(".welcome_profile_image").css("cursor","pointer");
						$(".welcome_profile_image").click(function(){
							$(".user_display_name a").trigger("click");
						});
				');
				$this->func->addContent($this->view->topbar('Meldungen von <a href="#" onclick="profile(' . (int)$foodsaver['id'] . ');return false;">' . $foodsaver['name'] . ' ' . $foodsaver['nachname'] . '</a>', \count($foodsaver['reports']) . ' gesamt', $this->func->avatar($foodsaver, 50)), CNT_TOP);
				$this->func->addContent($this->v_utils->v_field($this->wallposts('fsreport', (int)$_GET['id']), 'Notizen'));
				$this->func->addContent($this->view->listReportsTiny($foodsaver['reports']), CNT_RIGHT);
			}
		} else {
			$this->func->go('/?page=dashboard');
		}
	}
}
