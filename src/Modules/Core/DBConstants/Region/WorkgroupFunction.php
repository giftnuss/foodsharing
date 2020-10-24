<?php

namespace Foodsharing\Modules\Core\DBConstants\Region;

class WorkgroupFunction
{
	public const WELCOME = 1; // Begrüßungsteam
	public const VOTING = 2; // Abstimmung / Wahlen
	public const FSP = 3; // Fairteiler / FoodSharePoint

	public static function isValidFunction(int $value): bool
	{
		return in_array($value, range(self::WELCOME, self::FSP));
	}
}
