<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\Control;

class MapXhr extends Control
{
	public function __construct(Db $model, MapView $view)
	{
		$this->view = $view;
		$this->model = $model;

		parent::__construct();
	}

	public function savebpos()
	{
		$lat = (float)$_GET['lat'];
		$lon = (float)$_GET['lon'];

		$this->session->set('blocation', array(
			'lat' => $lat,
			'lon' => $lon
		));

		return array(
			'status' => 1
		);
	}
}
