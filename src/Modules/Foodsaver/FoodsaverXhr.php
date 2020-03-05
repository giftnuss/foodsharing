<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Services\NotificationService;
use Foodsharing\Services\SanitizerService;

class FoodsaverXhr extends Control
{
	private $foodsaverGateway;
	private $regionGateway;
	private $sanitizerService;
	private $notificationService;

	public function __construct(
		FoodsaverView $view,
		RegionGateway $regionGateway,
		SanitizerService $sanitizerService,
		FoodsaverGateway $foodsaverGateway,
		NotificationService $notificationService
	) {
		$this->view = $view;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionGateway = $regionGateway;
		$this->sanitizerService = $sanitizerService;
		$this->notificationService = $notificationService;

		parent::__construct();
	}

	public function loadFoodsaver()
	{
		if (!$this->session->may('orga') && !$this->session->isAdminFor($_GET['bid'])) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if ($foodsaver = $this->foodsaverGateway->loadFoodsaver($_GET['id'])) {
			$html = $this->view->foodsaverForm($foodsaver);

			return [
				'status' => 1,
				'script' => '$("#fsform").html(\'' . $this->sanitizerService->jsSafe($html) . '\');$(".button").button();$(".avatarlink img").load(function(){$(".avatarlink img").fadeIn();});'
			];
		}
	}

	/**
	 * xrh reload foodsaver list.
	 */
	public function foodsaverrefresh()
	{
		$regionId = $_GET['bid'];
		if (!$this->session->may('orga') && !$this->session->isAdminFor($regionId)) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$foodsaver = $this->foodsaverGateway->getFoodsaversByRegion($regionId);
		$regionData = $this->regionGateway->getRegion($regionId);
		$html = $this->sanitizerService->jsSafe($this->view->foodsaverList($foodsaver, $regionData), "'");

		return [
			'status' => 1,
			'script' => '$("#foodsaverlist").replaceWith(\'' . $html . '\');fsapp.init();'
		];
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
		$this->notificationService->sendEmailIfGroupHasNoAdmin($_GET['bid']);

		return [
			'status' => 1,
			'script' => 'pulseInfo("Foodsaver wurde entfernt");$("#fsform").html("");fsapp.refreshFoodsaver();'
		];
	}
}
