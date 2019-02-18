<?php

namespace Foodsharing\Modules\Core;

use InfluxDB\Point;

class InfluxMetrics
{
	private $influxdb;
	private $points;
	private $pageStatTags;
	private $pageStatFields;

	public function __construct(\InfluxDB\Database $influxdb)
	{
		$this->influxdb = $influxdb;
		$this->points = [];
		$this->pageStatTags = [];
		$this->pageStatFields = [];
	}

	public function __destruct()
	{
		$this->generatePageStatistics();
		$this->flush();
	}

	/**
	 * adds a point.
	 *
	 * @param string measurement name of the measurement
	 * @param array fields array of field => value for field values
	 * @param array tags array of tag => value for tag values
	 */
	public function addPoint($measurement, $tags = [], $fields = [])
	{
		$this->points[] = new Point($measurement,
			null,
			$tags,
			$fields);
	}

	/**
	 * writes all collected points to influxDb.
	 */
	public function flush()
	{
		$this->influxdb->writePoints($this->points);
		$this->points = [];
	}

	/**
	 * adds tags and fields used for per-execution statistics.
	 * Also enables generation of execution statistics as soon as this is called the first time.
	 *
	 * @param array $tags
	 * @param array $fields
	 */
	public function addPageStatData($tags = [], $fields = [])
	{
		$this->pageStatTags += $tags;
		$this->pageStatFields += $fields;
	}

	private function generatePageStatistics()
	{
		global $script_start_time;
		if (isset($script_start_time)) {
			$now = microtime(true);
			$executionTime = $now - $script_start_time;
			$this->addPageStatData([], ['execution_time' => $executionTime]);
			$this->addPoint('page', $this->pageStatTags, $this->pageStatFields);
		}
	}
}
