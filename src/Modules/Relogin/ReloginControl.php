<?php

namespace Foodsharing\Modules\Relogin;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;

class ReloginControl extends Control
{
	public function __construct(Model $model)
	{
		$this->model = $model;
		parent::__construct();
	}

	public function index()
	{
		$this->model->relogin();
		if (isset($_GET['url']) && !empty($_GET['url'])) {
			$url = urldecode($_GET['url']);
			if (substr($url, 0, 4) !== 'http') {
				$this->func->go($url);
			}
		}
		$this->func->go('/?page=dashboard');
	}
}
