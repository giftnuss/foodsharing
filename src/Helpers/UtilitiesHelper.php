<?php

namespace Foodsharing\Helpers;


class UtilitiesHelper
{
	public function preZero($i)
	{
		if ($i < 10) {
			return '0' . $i;
		}

		return $i;
	}
}
