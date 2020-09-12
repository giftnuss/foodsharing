<?php

namespace Foodsharing\Modules\Core;

use InfluxDB\Point;

class InfluxMetrics
{
	private \InfluxDB\Database $influxdb;
	private array $points;
	private array $pageStatTags;
	private array $pageStatFields;
	private array $mailStat;
	private array $dbStat;
	private int $scriptStartTime;

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
	 */
	public function addPoint(string $measurement, array $tags = [], array $fields = []): void
	{
		$this->points[] = new Point($measurement,
			null,
			$tags,
			$fields);
	}

	/**
	 * writes all collected points to influxDb.
	 */
	public function flush(): void
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
	 */
	public function addPageStatData(array $tags = [], array $fields = []): void
	{
		$this->pageStatTags += $tags;
		$this->pageStatFields += $fields;
	}

	private function generatePageStatistics(): void
	{
		$now = hrtime(true);
		$executionTime = $now - $this->scriptStartTime;
		$this->addPageStatData([], [
			'execution_time' => intdiv($executionTime, 1000 * 1000),
			'db_execution_time' => array_sum($this->dbStat),
			'db_queries' => count($this->dbStat),
			'db_execution_times' => implode(';', $this->dbStat)
		]);
		$this->addPoint('page', $this->pageStatTags, $this->pageStatFields);
	}

	private function generateMailStatistics(): void
	{
		foreach ($this->mailStat as $k => $v) {
			$this->addPoint('outgoing_email', ['template' => $k], ['count' => $v]);
		}
	}
}
