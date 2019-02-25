<?php

namespace Foodsharing\Helpers;

class StatusChecksHelper
{
	public function isMob(): bool
	{
		return isset($_SESSION['mob']) && $_SESSION['mob'] == 1;
	}

	public function submitted(): bool
	{
		return isset($_POST) && !empty($_POST);
	}
}
