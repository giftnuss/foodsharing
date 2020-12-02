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

	public function mayAdd(): bool
	{
		return $this->mayAdministrateBlog();
	}

	public function mayPublish(int $blogId): bool
	{
		return $this->mayAdd();
	}

	public function mayEdit(int $blogId): bool
	{
		return $this->mayAdministrateBlog();
	}

	public function mayDelete(int $blogId): bool
	{
		return $this->mayEdit($blogId);
	}

	public function mayAdministrateBlog(): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		// return $this->session->isAdminFor(RegionIDs:: whichever workgroup wants this );
		return false;
	}
}
