<?php

namespace Foodsharing\Modules\Activity\DTO;

class ImageActivityFilter
{
	public string $name;

	public int $id;

	public bool $included;

	public string $imageUrl;

	public static function create(string $name, int $id, bool $included, string $imageUrl): ImageActivityFilter
	{
		$option = new ImageActivityFilter();
		$option->name = $name;
		$option->id = $id;
		$option->included = $included;
		$option->imageUrl = $imageUrl;

		return $option;
	}
}
