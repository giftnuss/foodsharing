<?php

namespace Foodsharing\Lib\Db;

use Exception;
use Foodsharing\Debug\DebugBar;
use Foodsharing\Lib\Func;
use Foodsharing\Lib\Session;
use mysqli;

class Db
{
	/**
	 * @var mysqli
	 */
	private $mysqli;
	private $values;
	/**
	 * @var Func
	 */
	protected $func;

	/**
	 * @var Mem
	 */
	protected $mem;

	/**
	 * @var Session
	 */
	protected $session;

	public function __construct()
	{
		$this->values = array();
	}

	/**
	 * @required
	 */
	public function setFunc(Func $func)
	{
		$this->func = $func;
	}

	/**
	 * @required
	 */
	public function setMem(Mem $mem)
	{
		$this->mem = $mem;
	}

	/**
	 * @required
	 */
	public function setSession(Session $session)
	{
		$this->session = $session;
	}

	/**
	 * @required
	 */
	public function setMysqli(mysqli $mysqli)
	{
		$this->mysqli = $mysqli;
	}

	/**
	 * @deprecated use one of the new Database methods instead. if nothing else fits, use db->execute.
	 */
	public function sql($query)
	{
		$start = microtime(true);
		$res = $this->mysqli->query($query);
		$duration = microtime(true) - $start;

		if ($res == false) {
			error_log('SQL QUERY ERROR URL ' . ($_SERVER['REQUEST_URI'] ?? $_SERVER['argv'][0]) . ' IN ' . $query . ' : ' . $this->mysqli->error);
			DebugBar::addQuery($query, $duration, false, $this->mysqli->errno, $this->mysqli->error);
		} else {
			DebugBar::addQuery($query, $duration, true);
		}

		return $res;
	}

	/**
	 * @deprecated use db->fetchValue
	 */
	public function qOne($sql)
	{
		if ($res = $this->sql($sql)) {
			if ($row = $res->fetch_array()) {
				if (isset($row[0])) {
					return $row[0];
				}
			}
		}

		return false;
	}

	/**
	 * @deprecated use db->fetchAllValues
	 */
	public function qCol($sql)
	{
		$out = array();
		if ($res = $this->sql($sql)) {
			while ($row = $res->fetch_array()) {
				$out[] = $row[0];
			}
		}

		return $out;
	}

	/**
	 * Method to get an asoc array insted the colums are the keys
	 * so aftter all we can check like this if(isset($test[$key])) ...
	 *
	 * @param string $sql
	 *
	 * @return array |boolean
	 *
	 * @deprecated use db->fetchAllValues and adapt code to not use indexed array
	 */
	public function qColKey($sql)
	{
		$out = array();
		if ($res = $this->sql($sql)) {
			while ($row = $res->fetch_array()) {
				$val = (int)($row[0]);
				$out[$val] = $val;
			}
		}

		if (count($out) > 0) {
			return $out;
		}

		return false;
	}

	/**
	 * @deprecated use db->fetch
	 */
	public function qRow($sql)
	{
		try {
			$res = $this->sql($sql);

			if (is_object($res) && ($row = $res->fetch_assoc())) {
				foreach ($row as $i => $r) {
					$row[$i] = $r;
				}

				return $row;
			}
		} catch (Exception $e) {
		}

		return false;
	}

	/**
	 * @deprecated use db->delete
	 */
	public function del($sql)
	{
		if ($res = $this->sql($sql)) {
			return $this->mysqli->affected_rows;
		}

		return false;
	}

	/**
	 * @deprecated use db->insert
	 */
	public function insert($sql)
	{
		if ($res = $this->sql($sql)) {
			return $this->mysqli->insert_id;
		}

		return false;
	}

	/**
	 * @deprecated use db->update
	 */
	public function update($sql)
	{
		if ($this->sql($sql)) {
			return true;
		}

		return false;
	}

	/**
	 * @deprecated not needed when passing data as prepared statement
	 */
	public function dateval($val)
	{
		return '"' . $this->safe($val) . '"';
	}

	/**
	 * @deprecated use strip_tags() until the frontend can escape properly.
	 * String escaping is not needed anymore with prepared statements
	 */
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

	/**
	 * @deprecated use db->fetchAll
	 */
	public function q($sql)
	{
		$out = array();
		if ($res = $this->sql($sql)) {
			while ($row = $res->fetch_assoc()) {
				foreach ($row as $i => $r) {
					$row[$i] = $r;
				}
				$out[] = $row;
			}
		}

		return $out;
	}

	/**
	 * @deprecated Usage is not needed when passing data as prepared statement
	 */
	public function safe($str)
	{
		return $this->mysqli->escape_string($str);
	}

	/**
	 * @deprecated use db->fetchByCriteria instead
	 */
	public function getValues($fields, $table, $id)
	{
		$fields = implode('`,`', $fields);

		return $this->qRow('
			SELECT 	`' . $fields . '`
			FROM 	`fs_' . $table . '`
			WHERE 	`id` = ' . (int)$id . '
		');
	}

	/**
	 * @deprecated use db->fetchValueByCriteria instead if value is expected to exist, use db->fetchByCriteria instead
	 */
	public function getVal($field, $table, $id)
	{
		if (!isset($this->values[$field . '-' . $table . '-' . $id])) {
			$this->values[$field . '-' . $table . '-' . $id] = $this->qOne('
			SELECT 	`' . $field . '`
			FROM 	`fs_' . $table . '`
			WHERE 	`id` = ' . (int)$id . '
		');
		}

		return $this->values[$field . '-' . $table . '-' . $id];
	}

	/**
	 * @deprecated use db->update instead
	 */
	public function updateFields($fields, $table, $id)
	{
		$sql = array();
		foreach ($fields as $k => $f) {
			if (preg_replace('/[^0-9]/', '', $f) == $f) {
				$sql[] = '`' . $k . '`=' . (int)$f;
			} else {
				$sql[] = '`' . $k . '`=' . $this->strval($f);
			}
		}

		return $this->update('UPDATE `' . $table . '` SET ' . implode(',', $sql) . ' WHERE `id` = ' . (int)$id);
	}
}
