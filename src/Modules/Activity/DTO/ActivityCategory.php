<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityCategory
{
	public string $index;

	public string $name;

	public array $items;

	public static function create(string $index, string $name, array $items): ActivityCategory
	{
		$category = new ActivityCategory();
		$category->index = $index;
		$category->name = $name;
		$category->items = $items;

		return $category;
	}
}
