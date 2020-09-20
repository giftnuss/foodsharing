<?php

namespace Foodsharing\Modules\Application;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Utility\IdentificationHelper;

class ApplicationControl extends Control
{
	private $bezirk;
	private $bezirk_id;
	private $mode;
	private ApplicationGateway $gateway;
	private IdentificationHelper $identificationHelper;

	public function __construct(
		ApplicationGateway $gateway,
		ApplicationView $view,
		IdentificationHelper $identificationHelper
	) {
		$this->view = $view;
		$this->gateway = $gateway;
		$this->identificationHelper = $identificationHelper;

		parent::__construct();

		$this->bezirk_id = false;
		if (($this->bezirk_id = $this->identificationHelper->getGetId('bid')) === false) {
			$this->bezirk_id = $this->session->getCurrentRegionId();
		}

		$this->bezirk = false;
		if ($bezirk = $this->gateway->getRegion($this->bezirk_id)) {
			$this->bezirk = $bezirk;
		}

		$this->view->setBezirk($this->bezirk);

		if (!($this->session->isAdminFor($this->bezirk_id) || $this->session->may('orga'))) {
			$this->routeHelper->go('/');
		}
	}

	public function index(): void
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
