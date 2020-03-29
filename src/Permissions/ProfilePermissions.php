<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

class ProfilePermissions
{
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function maySeeEmailAddress(int $foodsharerId): bool
	{
		return $this->session->id() == $foodsharerId || $this->session->isOrgaTeam();
	}
}
