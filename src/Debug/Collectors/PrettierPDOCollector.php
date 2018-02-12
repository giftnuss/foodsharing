<?php

namespace Foodsharing\Debug\Collectors;

use DebugBar\DataCollector\PDO\PDOCollector;

class PrettierPDOCollector extends PDOCollector
{
	public function collect()
	{
		$data = parent::collect();
		foreach ($data['statements'] as $i => $statement) {
			$data['statements'][$i]['sql'] = $this->formatQueryStr($statement['sql']);
		}

		return $data;
	}

	private function formatQueryStr($str)
	{
		return strtr(preg_replace("/^\W+/", '', $str), ["\t" => '', "\r" => '', "\n" => ' ']);
	}
}
