<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

final class FAQPermissions
{
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function mayEditFAQ()
	{
		return $this->session->may('orga');
	}
}
