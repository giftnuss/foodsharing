<?php

namespace Foodsharing\Modules\Logout;

use Foodsharing\Lib\Session\S;

class LogoutControl
{
	public function __construct()
	{
		$this->model = new LoginModel();
		parent::__construct();
	}

	public function index()
	{
		$this->model->logout();
		$_SESSION['login'] = false;
		$_SESSION = array();
		S::destroy();
		header('Location: /');
		exit();
	}
}
