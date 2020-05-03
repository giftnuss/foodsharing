<?php

namespace Foodsharing\Modules\Core;

use InfluxDB\Point;

class InfluxMetrics
{
	private $influxdb;
	private $points;
	private $pageStatTags;
	private $pageStatFields;
	private $mailStat;
	private $dbStat;
	private $scriptStartTime;

	public function __construct(\InfluxDB\Database $influxdb)
	{
		$this->influxdb = $influxdb;
		$this->points = [];
		$this->pageStatTags = [];
		$this->pageStatFields = [];
		$this->mailStat = [];
		$this->dbStat = [];
		/* This is theoretically not the start time of the script - but it is quite close. */
		$this->scriptStartTime = hrtime(true);
	}

	public function __destruct()
	{
		$this->generatePageStatistics();
		$this->generateMailStatistics();
		$this->flush();
	}

	public function addOutgoingMail(string $template, int $count): void
	{
		if (array_key_exists($template, $this->mailStat)) {
			$this->mailStat[$template] += $count;
		} else {
			$this->mailStat[$template] = $count;
		}
	}

	public function addDbQuery(int $execution_ms): void
	{
		$this->dbStat[] = $execution_ms;
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
		try {
			@$this->influxdb->writePoints($this->points);
		} catch (\Exception $e) {
		}
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
		$now = hrtime(true);
		$executionTime = $now - $this->scriptStartTime;
		$this->addPageStatData([], [
			'execution_time' => intdiv($executionTime, 1e6),
			'db_execution_time' => array_sum($this->dbStat),
			'db_queries' => count($this->dbStat),
			'db_execution_times' => implode(';', $this->dbStat)
		]);
		$this->addPoint('page', $this->pageStatTags, $this->pageStatFields);
	}

	private function generateMailStatistics()
	{
		foreach ($this->mailStat as $k => $v) {
			$this->addPoint('outgoing_email', ['template' => $k], ['count' => $v]);
		}
	}
}
