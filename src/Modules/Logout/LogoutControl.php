<?php

namespace Foodsharing\Modules\Logout;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;

class LogoutControl extends Control
{
	public function __construct()
	{
		$this->model = new Model();
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
