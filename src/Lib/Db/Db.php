<?php

namespace Foodsharing\Lib\Db;

use Exception;
use Foodsharing\Debug\DebugBar;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\InfluxMetrics;
use mysqli;

class Db
{
	/**
	 * @var mysqli
	 */
	private $mysqli;
	private $values;

	/**
	 * @var DebugBar
	 */
	private $debug;

	/**
	 * @var Mem
	 */
	protected $mem;

	/**
	 * @var Session
	 */
	protected $session;

	/**
	 * @var InfluxMetrics
	 */
	protected $influxMetrics;

	public function __construct()
	{
		$this->values = [];
	}

	/**
	 * @required
	 */
	public function setDebug(DebugBar $debug)
	{
		$this->debug = $debug;
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
	public function setInfluxMetrics(InfluxMetrics $influxMetrics)
	{
		$this->influxMetrics = $influxMetrics;
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
		$start = hrtime(true);
		$res = $this->mysqli->query($query);
		$duration = intdiv(hrtime(true) - $start, 1e6);

		if ($res == false) {
			error_log('SQL QUERY ERROR URL ' . ($_SERVER['REQUEST_URI'] ?? $_SERVER['argv'][0]) . ' IN ' . $query . ' : ' . $this->mysqli->error);
			$this->debug->addQuery($query, $duration, false, $this->mysqli->errno, $this->mysqli->error);
		} else {
			$this->debug->addQuery($query, $duration, true);
		}
		$this->influxMetrics->addDbQuery($duration);

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
		$out = [];
		if ($res = $this->sql($sql)) {
			while ($row = $res->fetch_array()) {
				$out[] = $row[0];
			}
		}

		return $out;
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
	 * @deprecated use strip_tags() until the frontend can escape properly!
	 * (The string escaping part is not needed anymore with prepared statements)
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
		$out = [];
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
}
