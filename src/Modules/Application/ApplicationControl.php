<?php

namespace Foodsharing\Modules\Application;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;

class ApplicationControl extends Control
{
	private $bezirk;
	private $bezirk_id;
	private $mode;
	private $gateway;

	public function __construct(ApplicationGateway $gateway, ApplicationView $view)
	{
		$this->view = $view;
		$this->gateway = $gateway;

		parent::__construct();

		$this->bezirk_id = false;
		if (($this->bezirk_id = $this->func->getGetId('bid')) === false) {
			$this->bezirk_id = $this->session->getCurrentBezirkId();
		}

		$this->bezirk = false;
		if ($bezirk = $this->gateway->getRegion($this->bezirk_id)) {
			$big = array(Type::BIG_CITY => 1, Type::FEDERAL_STATE => 1, Type::COUNTRY => 1);
			if (isset($big[$bezirk['type']])) {
				$this->mode = 'big';
			} elseif ($bezirk['type'] == Type::WORKING_GROUP) {
				$this->mode = 'orgateam';
			}
			$this->bezirk = $bezirk;
		}

		$this->view->setBezirk($this->bezirk);

		if (!($this->session->isAdminFor($this->bezirk_id) || $this->session->may('orga'))) {
			$this->routeHelper->go('/');
		}
	}

	public function index()
	{
		if ($application = $this->gateway->getApplication($this->bezirk_id, $_GET['fid'])) {
			$this->pageHelper->addBread($this->bezirk['name'], '/?page=bezirk&bid=' . $this->bezirk_id);
			$this->pageHelper->addBread('Bewerbung von ' . $application['name'], '');
			$this->pageHelper->addContent($this->view->application($application));

			$this->pageHelper->addContent($this->v_utils->v_field(
				$this->wallposts('application', $application['id']),
				'Statusnotizen'
			));

			$this->pageHelper->addContent($this->view->applicationMenu($application), CNT_LEFT);
		}
	}
}
