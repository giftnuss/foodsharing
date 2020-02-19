<?php

namespace Foodsharing\Helpers;

class IdentificationHelper
{
	private $ids;

	public function __construct()
	{
		$this->ids = [];
	}

	public function getGetId($name)
	{
		if (isset($_GET[$name]) && (int)$_GET[$name] > 0) {
			return (int)$_GET[$name];
		}

		return false;
	}

	public function getAction($a): bool
	{
		return isset($_GET['a']) && $_GET['a'] == $a;
	}

	public function getActionId($a)
	{
		if (isset($_GET['a'], $_GET['id']) && $_GET['a'] == $a && (int)$_GET['id'] > 0) {
			return (int)$_GET['id'];
		}

		return false;
	}

	public function makeId($text, $ids = false)
	{
		$text = strtolower($text);
		str_replace(
			['ä', 'ö', 'ü', 'ß', ' '],
			['ae', 'oe', 'ue', 'ss', '_'],
			$text
		);
		$out = preg_replace('/[^a-z0-9_]/', '', $text);

		if ($ids !== false && isset($ids[$out])) {
			$id = $out;
			$i = 0;
			while (isset($ids[$id])) {
				++$i;
				$id = $out . '-' . $i;
			}
			$out = $id;
		}

		return $out;
	}

	public function id($name)
	{
		$id = $this->makeId($name, $this->ids);

		$this->ids[$id] = true;

		return $id;
	}
}
