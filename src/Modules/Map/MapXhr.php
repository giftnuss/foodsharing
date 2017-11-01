<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\Control;
use S;

class MapXhr extends Control
{
	public function __construct()
	{
		$this->view = new MapView();

		parent::__construct();
	}

	public function savebpos()
	{
		$lat = floatval($_GET['lat']);
		$lon = floatval($_GET['lon']);

		S::set('blocation', array(
			'lat' => $lat,
			'lon' => $lon
		));

		return array(
			'status' => 1
		);
	}
}
