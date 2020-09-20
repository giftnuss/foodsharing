<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

final class BlogPermissions
{
	private Session $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function mayAdd(int $regionId): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		return $this->session->isAdminFor($regionId);
	}

	public function mayPublish(int $regionId): bool
	{
		return $this->mayAdd($regionId);
	}

	public function mayEdit(array $authorOfPost): bool
	{
		if (!$authorOfPost) {
			return false;
		}
		if ($this->session->id() == $authorOfPost['foodsaver_id']) {
			return true;
		}

		return $this->session->isAdminFor($authorOfPost['bezirk_id']);
	}

	public function mayAdministrateBlog(): bool
	{
		return $this->session->isAdminForAWorkGroup() || $this->session->may('orga');
	}
}
