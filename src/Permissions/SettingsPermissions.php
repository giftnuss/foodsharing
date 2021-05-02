<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

class SettingsPermissions
{
	private Session $session;

	public function __construct(
		Session $session
	) {
		$this->session = $session;
	}

	public function mayUseCalendarExport(): bool
	{
		return $this->session->may('fs');
	}
}
