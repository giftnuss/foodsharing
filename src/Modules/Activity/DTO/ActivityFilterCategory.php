<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityFilterCategory
{
	public string $index;

	public string $name;

	public string $shortName;

	public array $items;

	public static function create(string $index, string $name, string $shortName, array $items): ActivityFilterCategory
	{
		$category = new ActivityFilterCategory();
		$category->index = $index;
		$category->name = $name;
		$category->shortName = $shortName;
		$category->items = $items;

		return $category;
	}
}
