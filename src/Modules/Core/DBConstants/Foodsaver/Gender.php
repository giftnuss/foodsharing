<?php

// table fs_foodsaver

namespace Foodsharing\Modules\Core\DBConstants\Foodsaver;

class Gender
{
	public const NOT_SELECTED = 0;
	public const MALE = 1;
	public const FEMALE = 2;
	public const DIVERSE = 3;

	/**
	 * Returns whether the value is a valid gender constant.
	 */
	public static function isValid(int $value): bool
	{
		return in_array($value, range(Gender::NOT_SELECTED, Gender::DIVERSE));
	}
}
