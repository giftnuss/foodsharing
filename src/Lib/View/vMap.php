<?php

namespace Foodsharing\Lib\View;

use Foodsharing\DI;
use Foodsharing\Lib\Func;
use Foodsharing\Modules\Core\Model;

class vMap extends vCore
{
	private $center;
	private $zoom;
	private $markercluster;
	private $searchpanel;
	private $defaultMarkerOptions;
	private $marker;

	/**
	 * @var Func
	 */
	private $func;

	/**
	 * @var Model
	 */
	private $model;

	public function __construct($center = false)
	{
		$this->func = DI::$shared->get(Func::class);
		$this->model = DI::$shared->get(Model::class);

		if (!$center) {
			$center = [50.89, 10.13];
		}
		$this->center = $center;

		$this->zoom = 13;
		$this->markercluster = false;
		$this->searchpanel = false;
		$this->defaultMarkerOptions = [
			'color' => 'orange',
			'icon' => 'smile',
			'prefix' => 'img'
		];
		$this->marker = array();
	}

	public function setSearchPanel($val)
	{
		$this->searchpanel = $val;
	}

	public function setDefaultMarkerOptions($icon, $color, $prefix = 'img')
	{
		$this->defaultMarkerOptions = [
			'icon' => $icon,
			'color' => $color,
			'prefix' => $prefix
		];
	}

	public function setLocation($lat, $lng)
	{
		$this->location = array($lat, $lng);
	}

	public function setMarkerCluster($val = true)
	{
		$this->markercluster = $val;
	}

	public function addMarker($lat, $lng, $marker = 'default')
	{
		$this->marker[] = [
			'lat' => $lat,
			'lng' => $lng
		];
	}

	public function render()
	{
		$mapOptions = [
			'center' => $this->center,
			'zoom' => $this->zoom,
			'markers' => $this->marker,
			'markercluster' => $this->markercluster,
			'searchpanel' => $this->searchpanel,
			'defaultMarkerOptions' => $this->defaultMarkerOptions
		];
		return '<div class="vmap" id="map" data-options="'. htmlspecialchars(json_encode($mapOptions)) .'"></div><input type="hidden" name="latlng" id="map-latLng" value="" />';
	}
}
