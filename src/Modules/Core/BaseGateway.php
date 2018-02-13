<?php

namespace Foodsharing\Modules\Core;

abstract class BaseGateway
{
	protected $db;

	public function __construct(Database $db)
	{
		$this->db = $db;
	}

	public function safe($str)
	{
		return $this->db->escape_string($str);
	}

	public function intval($val)
	{
		return (int)$val;
	}

	public function dateval($val)
	{
		return '"' . $this->safe($val) . '"';
	}

	public function floatval($val)
	{
		return floatval($val);
	}

	public function strval($val, $html = false)
	{
		if (is_string($html) || $html === false) {
			if (is_string($html)) {
				$val = strip_tags($val, $html);
			} else {
				$val = strip_tags($val);
			}
		}

		return '"' . $this->safe($val) . '"';
	}
}
