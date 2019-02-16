<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;

class FoodsaverXhr extends Control
{
	private $regionGateway;

	public function __construct(FoodsaverModel $model, FoodsaverView $view, RegionGateway $regionGateway)
	{
		$this->model = $model;
		$this->view = $view;
		$this->regionGateway = $regionGateway;

		parent::__construct();

		// permission check
		if (!$this->session->may('orga') && !$this->session->isAdminFor($_GET['bid'])) {
			return false;
		}
	}

	public function loadFoodsaver()
	{
		if ($foodsaver = $this->model->loadFoodsaver($_GET['id'])) {
			$html = $this->view->foodsaverForm($foodsaver);

			return array(
				'status' => 1,
				'script' => '$("#fsform").html(\'' . $this->func->jsSafe($html) . '\');$(".button").button();$(".avatarlink img").load(function(){$(".avatarlink img").fadeIn();});'
			);
		}
	}

	/**
	 * xrh reload foodsaver list.
	 */
	public function foodsaverrefresh()
	{
		$foodsaver = $this->model->listFoodsaver($_GET['bid']);
		$bezirk = $this->regionGateway->getBezirk($_GET['bid']);
		$html = $this->func->jsSafe($this->view->foodsaverList($foodsaver, $bezirk), "'");

		return array(
			'status' => 1,
			'script' => '$("#foodsaverlist").replaceWith(\'' . $html . '\');fsapp.init();'
		);
	}

	/**
	 * Method to delete an foodsaver from an bezirk.
	 */
	public function delfrombezirk()
	{
		$this->model->delfrombezirk($_GET['bid'], $_GET['id']);

		return array(
			'status' => 1,
			'script' => 'pulseInfo("Foodsaver wurde entfernt");$("#fsform").html("");fsapp.refreshfoodsaver();'
		);
	}
}
