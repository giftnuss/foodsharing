<?php

use Foodsharing\Modules\Core\Control;

class ActivityControl extends Control
{
	public function __construct()
	{
		$this->model = new ActivityModel();
		$this->view = new ActivityView();

		parent::__construct();
	}

	public function index()
	{
	}
}
