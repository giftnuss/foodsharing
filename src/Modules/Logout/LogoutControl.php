<?php

namespace Foodsharing\Modules\Logout;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;

class LogoutControl extends Control
{
	public function __construct(Model $model)
	{
		$this->model = $model;
		parent::__construct();
	}

	public function index()
	{
		$this->model->logout();
		$_SESSION['login'] = false;
		$_SESSION = array();
		$this->session->destroy();
		header('Location: /');
		exit();
	}
}
