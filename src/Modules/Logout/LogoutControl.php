<?php

namespace Foodsharing\Modules\Logout;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Modules\Core\Control;

class LogoutControl extends Control
{
	public function __construct(Db $model)
	{
		$this->model = $model;
		parent::__construct();
	}

	public function index()
	{
		Mem::logout($this->session->id());
		$_SESSION['login'] = false;
		$_SESSION = array();
		$this->session->destroy();
		header('Location: /');
		exit();
	}
}
