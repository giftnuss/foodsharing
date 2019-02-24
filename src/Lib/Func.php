<?php

namespace Foodsharing\Lib;

final class Func
{
	public function preZero($i)
	{
		if ($i < 10) {
			return '0' . $i;
		}

		return $i;
	}

	public function isMob(): bool
	{
		return isset($_SESSION['mob']) && $_SESSION['mob'] == 1;
	}

	public function submitted(): bool
	{
		return isset($_POST) && !empty($_POST);
	}
}
