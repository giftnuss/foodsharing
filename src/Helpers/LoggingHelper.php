<?php

namespace Foodsharing\Helpers;

class LoggingHelper
{
	public function info(string $msg, $title = false): void
	{
		$t = '';
		if ($title !== false) {
			$t = '<strong>' . $title . '</strong> ';
		}
		$_SESSION['msg']['info'][] = $t . $msg;
	}

	public function error(string $msg, $title = false): void
	{
		$t = '';
		if ($title !== false) {
			$t = '<strong>' . $title . '</strong> ';
		}
		$_SESSION['msg']['error'][] = $t . $msg;
	}
}
