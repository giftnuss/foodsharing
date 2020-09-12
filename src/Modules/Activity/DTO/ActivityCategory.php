<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityCategory
{
	public string $index;

	public string $name;

	public string $shortName;

	public array $items;

	public static function create(string $index, string $name, string $shortName, array $items): ActivityCategory
	{
		$category = new ActivityCategory();
		$category->index = $index;
		$category->name = $name;
		$category->shortName = $shortName;
		$category->items = $items;

		return $category;
	}
}
