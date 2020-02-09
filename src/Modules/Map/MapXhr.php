<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\Control;

class MapXhr extends Control
{
	public function __construct(MapView $view)
	{
		$this->view = $view;

		parent::__construct();
	}

	public function savebpos()
	{
		$lat = (float)$_GET['lat'];
		$lon = (float)$_GET['lon'];

		$this->session->set('blocation', [
			'lat' => $lat,
			'lon' => $lon
		]);

		return [
			'status' => 1
		];
	}
}
