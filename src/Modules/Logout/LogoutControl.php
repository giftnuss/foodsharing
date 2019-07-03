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
		$this->session->logout();
		header('Location: /');
		exit();
	}
}
