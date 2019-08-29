<?php

namespace Foodsharing\Modules\Logout;

use Foodsharing\Modules\Core\Control;

class LogoutControl extends Control
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$this->session->logout();
		header('Location: /');
		exit();
	}
}
