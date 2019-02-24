<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

final class BlogPermissions
{
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function canAdd(int $bezirkId): bool
	{
		return $this->session->isOrgaTeam() || $this->session->isAdminFor($bezirkId);
	}
}
