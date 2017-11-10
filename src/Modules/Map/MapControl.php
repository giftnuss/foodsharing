<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;

class MapControl extends Control
{
	public function __construct()
	{
		$this->view = new MapView();

		parent::__construct();
	}

	public function index()
	{
		addTitle(s('map'));
		$this->setTemplate('map');

		$center = $this->model->getValues(array('lat', 'lon'), 'foodsaver', fsId());
		addContent($this->view->mapControl(), CNT_TOP);

		$jsarr = '';
		if (isset($_GET['load']) && $_GET['load'] == 'baskets') {
			$jsarr = '["baskets"]';
		} elseif (isset($_GET['load']) && $_GET['load'] == 'fairteiler') {
			$jsarr = '["fairteiler"]';
		}

		addContent(
			$this->view->lMap($center)
		);

		if (S::may('fs') && isset($_GET['bid'])) {
			$center = $this->model->getValues(array('lat', 'lon'), 'betrieb', (int)$_GET['bid']);

			addJs('
				u_loadDialog("xhr.php?f=bBubble&id=' . (int)$_GET['bid'] . '");
			');
		}

		addJs('u_init_map();');

		if ($center) {
			addJs('u_map.setView([' . $center['lat'] . ',' . $center['lon'] . '],15);');
		}

		addJs('map.initMarker(' . $jsarr . ');');
	}
}
