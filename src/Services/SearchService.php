<?php

namespace Foodsharing\Services;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Buddy\BuddyGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreModel;
use Foodsharing\Modules\WorkGroup\WorkGroupGateway;

class SearchService
{
	private $buddyGateway;
	private $workGroupGateway;
	private $storeModel;
	private $regionGateway;
	private $session;
	private $sanitizerService;
	private $imageService;

	public function __construct(
		BuddyGateway $buddyGateway,
		WorkGroupGateway $workGroupGateway,
		StoreModel $storeModel,
		RegionGateway $regionGateway,
		Session $session,
		SanitizerService $sanitizerService,
		ImageService $imageService
	) {
		$this->buddyGateway = $buddyGateway;
		$this->workGroupGateway = $workGroupGateway;
		$this->storeModel = $storeModel;
		$this->regionGateway = $regionGateway;
		$this->session = $session;
		$this->sanitizerService = $sanitizerService;
		$this->imageService = $imageService;
	}

	/**
	 * Method to generate search Index for instant seach.
	 */
	public function generateIndex($fsId)
	{
		$index = [];

		/*
		 * Buddies Load persons in the index array that connected with the user
		*/
		if ($buddies = $this->buddyGateway->listBuddies($this->session->id())) {
			$result = [];
			foreach ($buddies as $b) {
				$img = '/img/avatar-mini.png';

				if (!empty($b['photo'])) {
					$img = $this->imageService->img($b['photo']);
				}

				$result[] = [
					'name' => $b['name'] . ' ' . $b['nachname'],
					'teaser' => '',
					'img' => $img,
					'click' => 'chat(\'' . $b['id'] . '\');',
					'id' => $b['id'],
					'search' => [
						$b['name'], $b['nachname']
					]
				];
			}
			$index[] = [
				'title' => 'Menschen die Du kennst',
				'key' => 'buddies',
				'result' => $result
			];
		}

		/*
		 * Groups load Groups connected to the user in the array
		*/
		if ($groups = $this->workGroupGateway->listMemberGroups($fsId)) {
			$result = [];
			foreach ($groups as $b) {
				$img = '/img/groups.png';
				if (!empty($b['photo'])) {
					$img = 'images/' . str_replace('photo/', 'photo/thumb_', $b['photo']);
				}
				$result[] = [
					'name' => $b['name'],
					'teaser' => $this->sanitizerService->tt($b['teaser'], 65),
					'img' => $img,
					'href' => '/?page=bezirk&bid=' . $b['id'] . '&sub=forum',
					'search' => [
						$b['name']
					]
				];
			}
			$index[] = [
				'title' => 'Deine Gruppen',
				'result' => $result
			];
		}

		/*
		 * Betriebe load food stores connected to the user in the array
		*/
		if ($betriebe = $this->storeModel->listMyBetriebe()) {
			$result = [];
			foreach ($betriebe as $b) {
				$result[] = [
					'name' => $b['name'],
					'teaser' => $b['str'] . ' ' . $b['hsnr'] . ', ' . $b['plz'] . ' ' . $b['stadt'],
					'href' => '/?page=fsbetrieb&id=' . $b['id'],
					'search' => [
						$b['name'], $b['str']
					]
				];
			}
			$index[] = [
				'title' => 'Deine Betriebe',
				'result' => $result
			];
		}

		/*
		 * Bezirke load Bezirke connected to the user in the array
		*/
		$bezirke = $this->regionGateway->listForFoodsaverExceptWorkingGroups($this->session->id());
		$result = [];
		foreach ($bezirke as $b) {
			$result[] = [
				'name' => $b['name'],
				'teaser' => '',
				'img' => false,
				'href' => '/?page=bezirk&bid=' . $b['id'] . '&sub=forum',
				'search' => [
					$b['name']
				]
			];
		}
		$index[] = [
			'title' => 'Deine Bezirke',
			'result' => $result
		];

		return $index;
	}
}
