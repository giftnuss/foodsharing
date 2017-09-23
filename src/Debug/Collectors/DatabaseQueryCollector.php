<?php

namespace Foodsharing\Debug\Collectors;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

/**
 *
 * A DebugBar collector to allow queries with timings to be registered manually.
 * Will render as a standard SQLQueriesWidget
 *
 * Based on https://github.com/maximebf/php-debugbar/issues/213#issuecomment-97033694
 * But We have to implement the query collecting ourselves.
 *
 */
class DatabaseQueryCollector extends DataCollector implements Renderable, AssetProvider
{
	private $queries = [];

	public function addQuery($query, $duration)
	{
		$this->queries[] = ['query' => self::formatQueryStr($query), 'duration' => $duration];
	}

	public function collect()
	{
		$queries = array();
		$totalExecTime = 0;
		foreach ($this->queries as $q) {
			$queries[] = array(
				'sql' => $q['query'],
				'duration' => $q['duration'],
				'duration_str' => $this->formatDuration($q['duration'])
			);
			$totalExecTime += $q['duration'];
		}

		return array(
			'nb_statements' => count($queries),
			'accumulated_duration' => $totalExecTime,
			'accumulated_duration_str' => $this->formatDuration($totalExecTime),
			'statements' => $queries
		);
	}

	public function getName()
	{
		return 'db';
	}

	public function getWidgets()
	{
		return array(
			"database" => array(
				"icon" => "arrow-right",
				"widget" => "PhpDebugBar.Widgets.SQLQueriesWidget",
				"map" => "db",
				"default" => "[]"
			),
			"database:badge" => array(
				"map" => "db.nb_statements",
				"default" => 0
			)
		);
	}

	public function getAssets()
	{
		return array(
			'css' => 'widgets/sqlqueries/widget.css',
			'js' => 'widgets/sqlqueries/widget.js'
		);
	}

	private function formatQueryStr($str)
	{
		return strtr(preg_replace("/^\W+/", "", $str), ["\t" => '', "\r" => '', "\n" => ' ']);
	}

}
