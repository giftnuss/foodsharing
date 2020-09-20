<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;

class SearchPermissions
{
	private Session $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function maySearchAllRegions(): bool
	{
		if ($this->session->may('orga')) {
			return true;
		}

		return $this->session->isAmbassador();
	}

	public function maySeeUserAddress(): bool
	{
		return $this->session->may('orga');
	}
}
