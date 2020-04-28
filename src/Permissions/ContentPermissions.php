<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

final class ContentPermissions
{
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function mayEditContent()
	{
		return $this->session->may('orga');
	}
}
