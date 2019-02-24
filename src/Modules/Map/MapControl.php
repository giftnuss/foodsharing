<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Control;

class MapControl extends Control
{
	public function __construct(Db $model, MapView $view)
	{
		$this->view = $view;
		$this->model = $model;

		parent::__construct();
	}

	public function index()
	{
		$this->pageCompositionHelper->addTitle($this->func->s('map'));
		$this->setTemplate('map');

		$center = $this->model->getValues(array('lat', 'lon'), 'foodsaver', $this->session->id());
		$this->pageCompositionHelper->addContent($this->view->mapControl(), CNT_TOP);

		$jsarr = '';
		if (isset($_GET['load']) && $_GET['load'] == 'baskets') {
			$jsarr = '["baskets"]';
		} elseif (isset($_GET['load']) && $_GET['load'] == 'fairteiler') {
			$jsarr = '["fairteiler"]';
		}

		$this->pageCompositionHelper->addContent(
			$this->view->lMap()
		);

		if ($this->session->may('fs') && isset($_GET['bid'])) {
			$center = $this->model->getValues(array('lat', 'lon'), 'betrieb', (int)$_GET['bid']);

			$this->pageCompositionHelper->addJs('
				u_loadDialog("/xhr.php?f=bBubble&id=' . (int)$_GET['bid'] . '");
			');
		}

		$this->pageCompositionHelper->addJs('u_init_map();');

		if ($center) {
			$this->pageCompositionHelper->addJs('u_map.setView([' . $center['lat'] . ',' . $center['lon'] . '],15);');
		}

		$this->pageCompositionHelper->addJs('map.initMarker(' . $jsarr . ');');
	}
}
