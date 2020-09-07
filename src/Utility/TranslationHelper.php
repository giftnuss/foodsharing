<?php

namespace Foodsharing\Utility;

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Gender;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;

final class TranslationHelper
{
	public function genderWord(int $gender, string $m, string $f, string $d): string
	{
		if ($gender == Gender::MALE) {
			$out = $m;
		} elseif ($gender == Gender::FEMALE) {
			$out = $f;
		} else {
			$out = $d;
		}

		return $out;
	}

	public function getRoleName(string $role, int $gender): string
	{
		$role = [
			Role::FOODSHARER => 'foodsharer',
			Role::FOODSAVER => 'foodsaver',
			Role::STORE_MANAGER => 'storemanager',
			Role::AMBASSADOR => 'ambassador',
			Role::ORGA => 'orga',
		][$role] ?? 'foodsharer';

		return $this->genderWord($gender,
			('terminology.' . $role . '.m'),
			('terminology.' . $role . '.f'),
			('terminology.' . $role . '.d')
		);
	}
}
