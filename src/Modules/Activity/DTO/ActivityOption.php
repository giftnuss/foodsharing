<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityOption
{
	public string $name;

	public int $id;

	public bool $checked;

	public static function create(string $name, int $id, bool $checked): ActivityOption
	{
		$option = new ActivityOption();
		$option->name = $name;
		$option->id = $id;
		$option->checked = $checked;

		return $option;
	}
}
