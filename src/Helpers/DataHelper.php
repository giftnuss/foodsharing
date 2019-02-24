<?php

namespace Foodsharing\Helpers;

class DataHelper
{
	public function setEditData($data): void
	{
		global $g_data;
		$g_data = $data;
	}

	public function getPostData(): array
	{
		if (isset($_POST)) {
			return $_POST;
		}

		return array();
	}

	public function getValue($id): string
	{
		global $g_data;

		if (isset($g_data[$id])) {
			return $g_data[$id];
		}

		return '';
	}

	public function unsetAll($array, $fields): array
	{
		$out = array();
		foreach ($fields as $f) {
			if (isset($array[$f])) {
				$out[$f] = $array[$f];
			}
		}

		return $out;
	}
}
