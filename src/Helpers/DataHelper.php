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

		return [];
	}

	public function getValue($id)
	{
		global $g_data;

		if (isset($g_data[$id])) {
			return $g_data[$id];
		}

		return '';
	}

	public function unsetAll($array, $fields): array
	{
		$out = [];
		foreach ($fields as $f) {
			if (isset($array[$f])) {
				$out[$f] = $array[$f];
			}
		}

		return $out;
	}

	/**
	 * Transforms an array into a associative array.
	 *
	 * @param array $data Array with a field labeled 'id', e.g.
	 *     <pre>array(['id' => 5, 'name' => 'foo'], ['id' => 42, 'name' => 'bar'])</pre>
	 *
	 * @return array Associative array with key 'id', e.g.
	 *     <pre>array([5] => ['id' => 5, 'name' => 'foo'], [42] => ['id' => 42, 'name' => 'bar'])</pre>
	 */
	public function useIdAsKey(array $data): array
	{
		$out = [];
		foreach ($data as $d) {
			$out[$d['id']] = $d;
		}

		return $out;
	}

	/**
	 * Creates a comma separated string of IDs.
	 *
	 * @param array $ids the IDs (may be of type <code>int</code> of <code>string</code>)
	 */
	public function commaSeparatedIds(array $ids): string
	{
		return implode(',', array_map('intval', $ids));
	}
}
