<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Services\SanitizerService;

class FoodsaverXhr extends Control
{
	private $foodsaverGateway;
	private $regionGateway;
	private $sanitizerService;

	public function __construct(
		FoodsaverModel $model,
		FoodsaverView $view,
		RegionGateway $regionGateway,
		SanitizerService $sanitizerService,
		FoodsaverGateway $foodsaverGateway
	) {
		$this->model = $model;
		$this->view = $view;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionGateway = $regionGateway;
		$this->sanitizerService = $sanitizerService;

		parent::__construct();
	}

	public function loadFoodsaver()
	{
		if (!$this->session->may('orga') && !$this->session->isAdminFor($_GET['bid'])) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if ($foodsaver = $this->model->loadFoodsaver($_GET['id'])) {
			$html = $this->view->foodsaverForm($foodsaver);

			return array(
				'status' => 1,
				'script' => '$("#fsform").html(\'' . $this->sanitizerService->jsSafe($html) . '\');$(".button").button();$(".avatarlink img").load(function(){$(".avatarlink img").fadeIn();});'
			);
		}
	}

	/**
	 * xrh reload foodsaver list.
	 */
	public function foodsaverrefresh()
	{
		if (!$this->session->may('orga') && !$this->session->isAdminFor($_GET['bid'])) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$foodsaver = $this->model->listFoodsaver($_GET['bid']);
		$bezirk = $this->regionGateway->getBezirk($_GET['bid']);
		$html = $this->sanitizerService->jsSafe($this->view->foodsaverList($foodsaver, $bezirk), "'");

		return array(
			'status' => 1,
			'script' => '$("#foodsaverlist").replaceWith(\'' . $html . '\');fsapp.init();'
		);
	}

	/**
	 * Method to delete a foodsaver from an region.
	 */
	public function deleteFromRegion()
	{
		if (!$this->session->may('orga') && !$this->session->isAdminFor($_GET['bid'])) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$this->foodsaverGateway->deleteFromRegion($_GET['bid'], $_GET['id']);

		return [
			'status' => 1,
			'script' => 'pulseInfo("Foodsaver wurde entfernt");$("#fsform").html("");fsapp.refreshfoodsaver();'
		];
	}
}
