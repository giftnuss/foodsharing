<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityFilter
{
	public string $name;

	public int $id;

	public bool $included;

	public static function create(string $name, int $id, bool $included): ActivityFilter
	{
		$option = new ActivityFilter();
		$option->name = $name;
		$option->id = $id;
		$option->included = $included;

		return $option;
	}
}
