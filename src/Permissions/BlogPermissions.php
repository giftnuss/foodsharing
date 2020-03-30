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

	public function mayAdd(int $regionId): bool
	{
		return $this->session->isOrgaTeam() || $this->session->isAdminFor($regionId);
	}

	public function mayEdit($authorOfPost): bool
	{
		if ($authorOfPost) {
			if ($this->session->id() == $authorOfPost['foodsaver_id'] || $this->session->isAdminFor($authorOfPost['bezirk_id'])) {
				return true;
			}
		}

		return false;
	}

	public function mayAdministrateBlog()
	{
		return $this->session->isAdminForAWorkGroup() || $this->session->may('orga');
	}
}
