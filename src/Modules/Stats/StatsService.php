<?php

namespace Foodsharing\Modules\Stats;

class StatsService
{
	public function gerettet_wrapper($id)
	{
		$ger = array(
			1 => 2,
			2 => 4,
			3 => 7.5,
			4 => 15,
			5 => 25,
			6 => 45,
			7 => 64
		);

		if (!isset($ger[$id])) {
			return 1.5;
		}

		return $ger[$id];
	}
}
