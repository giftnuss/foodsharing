<?php

namespace Foodsharing\Lib\View;

use Foodsharing\Modules\Core\DBConstants\Map\MapConstants;

class vMap extends vCore
{
	private $center;
	private $zoom;
	private $markercluster;
	private $searchpanel;
	private $defaultMarkerOptions;
	private $marker;

	public function __construct($center = false)
	{
		if (!$center) {
			$center = [
				'lat' => MapConstants::CENTER_GERMANY_LAT,
				'lon' => MapConstants::CENTER_GERMANY_LON
			];
		}
		$this->center = $center;

		$this->zoom = MapConstants::ZOOM_CITY;
		$this->markercluster = false;
		$this->searchpanel = false;
		$this->defaultMarkerOptions = [
			'color' => 'orange',
			'icon' => 'smile',
			'prefix' => 'fa'
		];
		$this->marker = [];
	}

	public function setSearchPanel($val)
	{
		$this->searchpanel = $val;
	}

	public function setDefaultMarkerOptions(string $icon, string $color, string $prefix = 'fa'): void
	{
		$this->defaultMarkerOptions = [
			'icon' => $icon,
			'color' => $color,
			'prefix' => $prefix
		];
	}

	public function setCenter($lat, $lng)
	{
		$this->center = [$lat, $lng];
	}

	public function setZoom(int $zoom)
	{
		$this->zoom = $zoom;
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

		return '<div class="vmap" id="map" data-options="' . htmlspecialchars(json_encode($mapOptions)) . '"></div><input type="hidden" name="latlng" id="map-latLng" value="" />';
	}
}
