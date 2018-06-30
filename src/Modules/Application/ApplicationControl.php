<?php

namespace Foodsharing\Modules\Application;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;

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
			$this->bezirk_id = $this->func->getBezirkId();
		}

		$this->bezirk = false;
		if ($bezirk = $this->gateway->getRegion($this->bezirk_id)) {
			$big = array(8 => 1, 5 => 1, 6 => 1);
			if (isset($big[$bezirk['type']])) {
				$this->mode = 'big';
			} elseif ($bezirk['type'] == 7) {
				$this->mode = 'orgateam';
			}
			$this->bezirk = $bezirk;
		}

		$this->view->setBezirk($this->bezirk);

		if (!($this->func->isBotFor($this->bezirk_id) || S::may('orga'))) {
			$this->func->go('/');
		}
	}

	public function index()
	{
		if ($application = $this->gateway->getApplication($this->bezirk_id, $_GET['fid'])) {
			$this->func->addBread($this->bezirk['name'], '/?page=bezirk&bid=' . $this->bezirk_id);
			$this->func->addBread('Bewerbung von ' . $application['name'], '');
			$this->func->addContent($this->view->application($application));

			$this->func->addContent($this->v_utils->v_field(
				$this->wallposts('application', $application['id']),
				'Statusnotizen'
			));

			$this->func->addContent($this->view->applicationMenu($application), CNT_LEFT);
		}
	}
}
