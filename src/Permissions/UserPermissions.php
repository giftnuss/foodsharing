<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

final class UserPermissions
{
	private Session $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function maySeeUserDetails(int $userId): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		return $userId === $this->session->id();
	}
}
