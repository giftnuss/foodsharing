<?php

namespace Foodsharing\Modules\NewArea;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;

class NewareaControl extends Control
{
	public function __construct()
	{
		$this->model = new NewareaModel();
		$this->view = new NewareaView();

		parent::__construct();

		if (!S::may('orga')) {
			$this->func->go('/?page=dashboard');
		}
	}

	public function index()
	{
		$this->func->addBread('Anfragen für neue Bezirke');
		if ($foodsaver = $this->model->getWantNews()) {
			$this->func->addContent($this->view->listWantNews($foodsaver));

			$this->func->addContent($this->view->orderToBezirk(), CNT_RIGHT);

			$this->func->addContent($this->view->options(), CNT_RIGHT);
		}
	}
}
