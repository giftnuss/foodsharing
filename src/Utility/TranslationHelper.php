<?php

namespace Foodsharing\Utility;

use Foodsharing\Modules\Core\DBConstants\Foodsaver\Gender;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;

final class TranslationHelper
{
	public function getTranslations(): array
	{
		global $g_lang;

		return $g_lang;
	}

	public function s($id)
	{
		global $g_lang;

		if (isset($g_lang[$id])) {
			return $g_lang[$id];
		}

		return $id;
	}

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
