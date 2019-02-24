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

	public function mayAdd(int $bezirkId): bool
	{
		return $this->session->isOrgaTeam() || $this->session->isAdminFor($bezirkId);
	}


	public function mayEdit($val): bool
	{
		if ($val) {
			if ($this->session->id() == $val['foodsaver_id'] || $this->session->isAdminFor($val['bezirk_id'])) {
				return true;
			}
		}

		return false;
	}
}
