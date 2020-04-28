<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

final class UserPermissions
{
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function maySeeUserDetails(int $userId): bool
	{
		return $userId === $this->session->id() || $this->session->isOrgaTeam();
	}
}
