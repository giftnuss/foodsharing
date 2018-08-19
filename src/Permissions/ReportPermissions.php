<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

class ReportPermissions
{
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function mayAccessReportsForRegion($regionId)
	{
		return $this->session->isAdminFor($regionId);
	}
}
