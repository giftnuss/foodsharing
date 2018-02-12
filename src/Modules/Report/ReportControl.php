<?php

namespace Foodsharing\Modules\Report;

use Foodsharing\Modules\Core\Control;

class ReportControl extends Control
{
	public function __construct()
	{
		$this->model = new ReportModel();
		$this->view = new ReportView();

		parent::__construct();

		if (!isset($_GET['sub'])) {
			$this->func->go('/?page=report&sub=uncom');
		}
	}

	public function index()
	{
		if ($this->func->mayHandleReports()) {
			$this->func->addBread('Reportmeldungen', '/?page=report');
		} else {
			$this->func->go('/?page=dashboard');
		}
	}

	public function uncom()
	{
		if ($this->func->mayHandleReports()) {
			$this->func->addContent($this->view->statsMenu($this->model->getReportStats()), CNT_LEFT);

			$reports = array();
			if ($reports = $this->model->getReports(0)) {
				$this->func->addContent($this->view->listReports($reports));
			}
			$this->func->addContent($this->view->topbar('Neue Verstoßmeldungen', count($reports) . ' insgesamt', '<img src="/img/shit.png" />'), CNT_TOP);
		}
	}

	public function com()
	{
		if ($this->func->mayHandleReports()) {
			$this->func->addContent($this->view->statsMenu($this->model->getReportStats()), CNT_LEFT);

			$reports = array();
			if ($reports = $this->model->getReports(1)) {
				$this->func->addContent($this->view->listReports($reports));
			}
			$this->func->addContent($this->view->topbar('Bestätigte Verstoßmeldungen', count($reports) . ' insgesamt', '<img src="/img/shit.png" />'), CNT_TOP);
		}
	}

	public function foodsaver()
	{
		if ($this->func->mayHandleReports()) {
			if ($foodsaver = $this->model->getReportedSaver($_GET['id'])) {
				$this->func->addBread('Reportmeldungen', '/?page=report&sub=foodsaver&id=' . (int)$foodsaver['id']);
				$this->func->addJs('
						$(".welcome_profile_image").css("cursor","pointer");
						$(".welcome_profile_image").click(function(){
							$(".user_display_name a").trigger("click");
						});
				');
				$this->func->addContent($this->view->topbar('Meldungen von <a href="#" onclick="profile(' . (int)$foodsaver['id'] . ');return false;">' . $foodsaver['name'] . ' ' . $foodsaver['nachname'] . '</a>', count($foodsaver['reports']) . ' gesamt', $this->func->avatar($foodsaver, 50)), CNT_TOP);
				$this->func->addContent($this->v_utils->v_field($this->wallposts('fsreport', (int)$_GET['id']), 'Notizen'));
				$this->func->addContent($this->view->listReportsTiny($foodsaver['reports']), CNT_RIGHT);
			}
		} else {
			$this->func->go('/?page=dashboard');
		}
	}
}
