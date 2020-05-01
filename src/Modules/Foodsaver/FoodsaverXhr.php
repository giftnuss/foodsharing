<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\Services\NotificationService;
use Foodsharing\Services\SanitizerService;

class FoodsaverXhr extends Control
{
	private $foodsaverGateway;
	private $regionGateway;
	private $sanitizerService;
	private $regionPermissions;
	private $notificationService;

	public function __construct(
		FoodsaverView $view,
		RegionGateway $regionGateway,
		SanitizerService $sanitizerService,
		RegionPermissions $regionPermissions,
		FoodsaverGateway $foodsaverGateway,
		NotificationService $notificationService
	) {
		$this->view = $view;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionGateway = $regionGateway;
		$this->sanitizerService = $sanitizerService;
		$this->regionPermissions = $regionPermissions;
		$this->notificationService = $notificationService;

		parent::__construct();
	}

	public function loadFoodsaver()
	{
		$regionId = $_GET['bid'];
		if (!$this->regionPermissions->mayHandleFoodsaverRegionMenu($regionId)) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$foodsaverId = $_GET['id'];
		if ($foodsaver = $this->foodsaverGateway->loadFoodsaver($foodsaverId)) {
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
		if (!$this->regionPermissions->mayHandleFoodsaverRegionMenu($regionId)) {
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
		$regionId = $_GET['bid'];
		$foodsaverId = $_GET['id'];
		if (!$this->regionPermissions->mayDeleteFoodsaverFromRegion($regionId)) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$this->foodsaverGateway->deleteFromRegion($regionId, $foodsaverId);

		return [
			'status' => 1,
			'script' => 'pulseInfo("Foodsaver wurde entfernt");$("#fsform").html("");fsapp.refreshFoodsaver();'
		];
	}
}
