<?php

namespace Foodsharing\Debug\Collectors;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

/**
 * A DebugBar collector to allow queries with timings to be registered manually.
 * Will render as a standard SQLQueriesWidget.
 *
 * Based on https://github.com/maximebf/php-debugbar/issues/213#issuecomment-97033694
 * But We have to implement the query collecting ourselves.
 */
class DatabaseQueryCollector extends DataCollector implements Renderable, AssetProvider
{
	private $queries = [];

	public function addQuery($query)
	{
		$this->queries[] = $query;
	}

	public function collect()
	{
		$localQueries = [];
		$totalExecTimeInSeconds = 0;
		foreach ($this->queries as $q) {
			[$sql, $durationInSeconds, $success, $error_code, $error_message] = $q;
			$localQueries[] = [
				'sql' => $this->formatQueryStr($sql),
				'duration' => $durationInSeconds,
				'duration_str' => $this->getDataFormatter()->formatDuration($durationInSeconds),
				'is_success' => $success,
				'error_code' => $error_code,
				'error_message' => $error_message
			];
			$totalExecTimeInSeconds += $durationInSeconds;
		}

		return [
			'nb_statements' => count($localQueries),
			'accumulated_duration' => $totalExecTimeInSeconds,
			'accumulated_duration_str' => $this->getDataFormatter()->formatDuration($totalExecTimeInSeconds),
			'statements' => $localQueries
		];
	}

	public function getName()
	{
		return 'db';
	}

	public function getWidgets()
	{
		return [
			'database (mysqli)' => [
				'icon' => 'arrow-right',
				'widget' => 'PhpDebugBar.Widgets.SQLQueriesWidget',
				'map' => 'db',
				'default' => '[]'
			],
			'database (mysqli):badge' => [
				'map' => 'db.nb_statements',
				'default' => 0
			]
		];
	}

	public function getAssets()
	{
		return [
			'css' => 'widgets/sqlqueries/widget.css',
			'js' => 'widgets/sqlqueries/widget.js'
		];
	}

	private function formatQueryStr($str)
	{
		return strtr(preg_replace("/^\W+/", '', $str), ["\t" => '', "\r" => '', "\n" => ' ']);
	}
}
