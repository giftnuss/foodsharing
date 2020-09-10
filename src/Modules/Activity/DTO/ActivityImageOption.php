<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityImageOption
{
	public string $name;

	public int $id;

	public bool $checked;

	public string $imageUrl;

	public static function create(string $name, int $id, bool $checked, string $imageUrl): ActivityImageOption
	{
		$option = new ActivityImageOption();
		$option->name = $name;
		$option->id = $id;
		$option->checked = $checked;
		$option->imageUrl = $imageUrl;

		return $option;
	}
}
