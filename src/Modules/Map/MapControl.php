<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\Control;

class MapControl extends Control
{
	private $mapGateway;

	public function __construct(MapGateway $mapGateway, MapView $view)
	{
		$this->view = $view;
		$this->mapGateway = $mapGateway;

		parent::__construct();
	}

	public function index()
	{
		$this->pageHelper->addTitle($this->translationHelper->s('map'));
		$this->setTemplate('map');

		if ($this->session->may()) {
			$center = $this->mapGateway->getFoodsaverLocation($this->session->id());
		}
		$this->pageHelper->addContent($this->view->mapControl(), CNT_TOP);

		$jsarr = '';
		if (isset($_GET['load']) && $_GET['load'] == 'baskets') {
			$jsarr = '["baskets"]';
		} elseif (isset($_GET['load']) && $_GET['load'] == 'fairteiler') {
			$jsarr = '["fairteiler"]';
		}

		$this->pageHelper->addContent(
			$this->view->lMap()
		);

		if ($this->session->may('fs') && isset($_GET['bid'])) {
			$center = $this->mapGateway->getStoreLocation($_GET['bid']);
			// (panschk) whitespace matters here -- we need line break after the method call for javascript to compile
			$this->pageHelper->addJs('
				ajreq(\'bubble\', { app: \'store\', id: ' . $_GET['bid'] . ' })
			');
		}

		$this->pageHelper->addJs('u_init_map();');

		if (!empty($center)) {
			if ($center['lat'] == 0 && $center['lon'] == 0) {
				$this->pageHelper->addJs('u_map.fitBounds([[46.0, 4.0],[55.0, 17.0]]);');
			} else {
				$this->pageHelper->addJs('u_map.setView([' . $center['lat'] . ',' . $center['lon'] . '],15);');
			}
		}

		$this->pageHelper->addJs('map.initMarker(' . $jsarr . ');');
	}
}
