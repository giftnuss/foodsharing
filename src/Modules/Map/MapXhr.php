<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Model;

class MapXhr extends Control
{
	public function __construct(Model $model, MapView $view)
	{
		$this->view = $view;
		$this->model = $model;

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
