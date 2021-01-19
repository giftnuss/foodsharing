<?php

namespace Foodsharing\Modules\Map\DTO;

class MapMarker
{
	/**
	 * ID of the object with which the marker is associated.
	 */
	public int $id;

	/**
	 * Coordinates of the marker.
	 */
	public float $lat;
	public float $lon;

	/**
	 * The region in which the object of this marker is.
	 */
	public ?int $regionId;

	public static function create(
		int $id,
		float $lat,
		float $lon,
		?int $regionId = null
	): MapMarker {
		$marker = new MapMarker();
		$marker->id = $id;
		$marker->lat = $lat;
		$marker->lon = $lon;
		$marker->regionId = $regionId;

		return $marker;
	}
}
