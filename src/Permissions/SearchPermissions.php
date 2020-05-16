<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

class SearchPermissions
{
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function maySearchAllRegions(): bool
	{
		return $this->session->isAmbassador() || $this->session->isOrgaTeam();
	}
}
