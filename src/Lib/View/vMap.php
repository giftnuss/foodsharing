<?php

namespace Foodsharing\Lib\View;

use Foodsharing\DI;
use Foodsharing\Lib\Func;
use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Model;

class vMap extends vCore
{
	private $id;
	private $location;
	private $zoom;
	private $markercluster;
	private $searchpanel;
	private $default_marker;
	private $provider;
	private $provider_options;
	private $marker;
	private $home_marker;
	private $func;
	private $doGeoIPLookup = false;
	/**
	 * @var Model
	 */
	private $model;

	public function __construct($id = 'map', $center = false)
	{
		$this->func = DI::$shared->get(Func::class);
		$this->id = $this->id($id);
		$this->model = DI::$shared->get(Model::class);

		if (!$center) {
			$this->doGeoIPLookup = true;
			$center = [50.89, 10.13];
		}
		$this->location = $center;

		$this->zoom = 13;
		$this->markercluster = false;
		$this->searchpanel = false;
		$this->default_marker = array(
			'color' => 'orange',
			'icon' => 'smile',
			'prefix' => 'img'
		);
		$this->provider_options = array();
		$this->marker = array();
		$this->setProvider('esri');
		$this->home_marker = false;
	}

	public function setSearchPanel($val)
	{
		$this->searchpanel = $val;
	}

	public function setProvider($provider, $options = array())
	{
		$this->provider = $provider;
		$this->provider_options = $options;
	}

	public function setProviderOsm()
	{
		$this->setProvider('osm');
	}

	public function setProviderCloudMade($style_id = '113144', $api_key = '182f97c8608145c09f1fce4a266ed61f')
	{
		$this->setProvider('cloudmade', array(
			'key' => $api_key,
			'styleId' => $style_id
		));
	}

	public function setDefaultMarker($icon, $color, $prefix = 'img')
	{
		$this->default_marker = array(
			'icon' => $icon,
			'color' => $color,
			'prefix' => $prefix
		);
	}

	public function setHomeMarker()
	{
		$this->home_marker = true;
	}

	public function setProviderMapbox($map = 'hlekmb0m', $user = 'raffelrocker')
	{
		$this->setProvider('mapbox', array(
			'user' => $user,
			'map' => $map
		));
	}

	public function setZoom($zoom)
	{
		$this->zoom = (int)$zoom;
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
		$this->marker[] = array(
			'lat' => $lat,
			'lng' => $lng
		);
	}

	public function render()
	{
		$this->func->addJsFunc('
			var ' . $this->id . '_latLng = [' . $this->location[0] . ',' . $this->location[1] . '];
			var ' . $this->id . '_defaultIcon = L.AwesomeMarkers.icon({
			    icon: "' . $this->default_marker['icon'] . '",
			    markerColor: "' . $this->default_marker['color'] . '",
			    prefix: "' . $this->default_marker['prefix'] . '"
			});
		');
		if ($this->home_marker) {
			$this->func->addJsFunc('
			var ' . $this->id . '_home_marker = null;		
			var ' . $this->id . '_homeIcon = L.icon({   iconUrl: "/css/img/marker-home.png",shadowUrl: \'/css/img/default-shadow.png\',iconSize: [25, 41],	iconAnchor: [12, 41],popupAnchor: [1, -34],shadowSize: [46, 41],shadowAnchor: [12, 41]});
			');
		}

		if ($this->markercluster) {
			//$this->func->addScriptTop('/js/leaflet.markercluster.js');
			$this->func->addCss('/js/markercluster/dist/MarkerCluster.css');
			$this->func->addCss('/js/markercluster/dist/MarkerCluster.Default.css');
		}

		$this->func->addJsFunc('
			var ' . $this->id . ' = null;
			var ' . $this->id . '_markers = null;	
			var ' . $this->id . '_geocoder = null;	
		');

		$this->func->addJs('' . $this->id . ' = L.map("' . $this->id . '").setView([' . $this->location[0] . ', ' . $this->location[1] . '], ' . $this->zoom . ');');
		if ($this->searchpanel !== false) {
			$hm = '';
			if ($this->home_marker) {
				$hm = $this->id . '_home_marker.setLatLng(new L.LatLng(latLng[0], latLng[1])).update();';
			}

			$this->func->addJs('
				$.getScript( "https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=' . GOOGLE_API_KEY . '", function() {
					var addressPicker = new AddressPicker({
						map: {
							map: ' . $this->id . '
						}
					});
		
					$(\'#' . $this->searchpanel . '\').typeahead(null, {
					  displayKey: \'description\',
					  source: addressPicker.ttAdapter()
					});
					addressPicker.bindDefaultTypeaheadEvent($(\'#' . $this->searchpanel . '\'))
				});
			');
		}
		switch ($this->provider) {
			case 'osm':
				$this->func->addJs('
					L.tileLayer(\'http://{s}.tile.osm.org/{z}/{x}/{y}.png\', {
						attribution: \'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors\'
					}).addTo(' . $this->id . ');
				');
				break;

			case 'esri':
				$this->func->addJs('
					L.tileLayer(\'https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}\', {
						attribution: \'Geocoding by <a href=\"https://google.com\">Google</a>, Tiles &copy; Esri 2014\'
					}).addTo(' . $this->id . ');
				');
				break;
			case 'mapquest':
				$this->func->addJs('
					L.tileLayer(\'http://{s}.mqcdn.com/tiles/1.0.0/vy/map/{z}/{x}/{y}.png\', {
						subdomains:["mtile01","mtile02","mtile03","mtile04"],
						attributionControl: false
					}).addTo(' . $this->id . ');
				');
				break;

			case 'mapbox':
				$this->func->addJs('
					L.tileLayer(\'http://{s}.tiles.mapbox.com/v3/' . $this->provider_options['user'] . '.' . $this->provider_options['map'] . '/{z}/{x}/{y}.png\', {
					    attributionControl: false
					}).addTo(' . $this->id . ');
				');
				break;

			case 'cloudmade':
				$this->func->addJs('
					L.tileLayer(\'http://{s}.tile.cloudmade.com/' . $this->provider_options['key'] . '/' . $this->provider_options['styleId'] . '/256/{z}/{x}/{y}.png\', {
					    attributionControl: false
					}).addTo(' . $this->id . ');
				');
				break;
		}
		if (!empty($this->marker)) {
			foreach ($this->marker as $m) {
				$this->func->addJs('L.marker([' . $m['lat'] . ', ' . $m['lng'] . '],{icon:' . $this->id . '_defaultIcon}).addTo(' . $this->id . ');');
			}
		}

		if ($this->home_marker) {
			$this->func->addJs($this->id . '_home_marker = L.marker([' . $this->location[0] . ', ' . $this->location[1] . '],{icon:' . $this->id . '_homeIcon}).addTo(' . $this->id . ');');
		}

		if ($this->markercluster) {
			$this->func->addJsFunc('
			function ' . $this->id . '_clearCluster()
			{
				if(' . $this->id . '_markers != null && ' . $this->id . '_markers != undefined)
				{
					' . $this->id . '.removeLayer(' . $this->id . '_markers);
				}
				' . $this->id . '_markers = L.markerClusterGroup();
			}
				
			function ' . $this->id . '_addMarker(options)
			{
				if(options.icon == undefined)
				{
					options.icon = ' . $this->id . '_defaultIcon;
				}
				
				var marker = L.marker(new L.LatLng(options.lat, options.lng), { id: options.id, click: options.click, icon: options.icon });
				//marker.bindPopup(id);
				' . $this->id . '_markers.addLayer(marker);
			}
				
			function ' . $this->id . '_commitCluster()
			{
				' . $this->id . '_markers.on("click", function(el){	
					if(el.layer.options.click != undefined)
					{
						el.layer.options.click();
					}
				});
				' . $this->id . '.addLayer(' . $this->id . '_markers);
			}
			');
		} else {
			$this->func->addJsFunc('
			function ' . $this->id . '_addMarker(lat,lng,id)
			{
				L.marker([lat, lng]).addTo(' . $this->id . ');	
			}	
			');
		}

		/*
		 * Disable GeoIPLookup as
		 * * we are injecting external javascript
		 * * the service seemed to be down breaking the page on 2018-02-16
		 */
		/*
		 if ($this->doGeoIPLookup) {
			$this->addGeoIPLookup();
			$this->doGeoIPLookup = false;
		}
		*/

		return '
		<div class="vmap" id="' . $this->id . '"></div><input type="hidden" name="latlng" id="' . $this->id . '-latLng" value="" />';
	}

	private function addGeoIPLookup()
	{
		$this->func->addJs('
		$.getJSON("http://www.geoplugin.net/json.gp?ip=' . $_SERVER['REMOTE_ADDR'] . '&jsoncallback=?", function(data) {
			if(data.geoplugin_status != undefined && data.geoplugin_status >= 200 && data.geoplugin_status < 300)
			{
				$.getJSON("http://www.geoplugin.net/extras/postalcode.gp?lat="+data.geoplugin_latitude+"&long="+data.geoplugin_longitude+"&format=json&jsoncallback=?", function(plz){
					if(plz.geoplugin_place != undefined)
					{
						' . $this->id . '_latLng = [data.geoplugin_latitude, data.geoplugin_longitude];
						' . $this->id . '.setView([data.geoplugin_latitude, data.geoplugin_longitude],' . (int)$this->zoom . ');
						ajreq({
							app:"karte",
							action:"setlocation",
							data: {
								lat: data.geoplugin_latitude,
								lng: data.geoplugin_longitude,
								city: plz.geoplugin_place,
								zip: plz.geoplugin_postCode
							}
						});
					}
				});
			}
		});
	');
	}
}
