<?php

namespace Foodsharing\Modules\Logout;

use Foodsharing\Lib\Db\Db;
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
		$this->mem->logout($this->session->id());
		$_SESSION['login'] = false;
		$_SESSION = array();
		$this->session->destroy();
		header('Location: /');
		exit();
	}
}
