<?php

namespace Foodsharing\Helpers;

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

	public function sv($id, $var)
	{
		global $g_lang;
		if (is_array($var)) {
			$search = [];
			$replace = [];
			foreach ($var as $key => $value) {
				$search[] = '{' . $key . '}';
				$replace[] = $value;
			}

			return str_replace($search, $replace, $g_lang[$id]);
		}

		return str_replace('{var}', $var, $g_lang[$id]);
	}

	public function genderWord($gender, $m, $w, $other)
	{
		$out = $other;
		if ($gender == 1) {
			$out = $m;
		} elseif ($gender == 2) {
			$out = $w;
		}

		return $out;
	}
}
