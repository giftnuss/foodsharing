<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Buddy\BuddyGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\WorkGroup\WorkGroupGateway;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\Sanitizer;

class SearchIndexGenerator
{
	private BuddyGateway $buddyGateway;
	private WorkGroupGateway $workGroupGateway;
	private StoreGateway $storeGateway;
	private RegionGateway $regionGateway;
	private Session $session;
	private Sanitizer $sanitizerService;
	private ImageHelper $imageHelper;

	public function __construct(
		BuddyGateway $buddyGateway,
		WorkGroupGateway $workGroupGateway,
		StoreGateway $storeGateway,
		RegionGateway $regionGateway,
		Session $session,
		Sanitizer $sanitizerService,
		ImageHelper $imageHelper
	) {
		$this->buddyGateway = $buddyGateway;
		$this->workGroupGateway = $workGroupGateway;
		$this->storeGateway = $storeGateway;
		$this->regionGateway = $regionGateway;
		$this->session = $session;
		$this->sanitizerService = $sanitizerService;
		$this->imageHelper = $imageHelper;
	}

	/**
	 * Generate the search index for instant search.
	 */
	public function generateIndex($fsId): array
	{
		$userId = $this->session->id();
		$index = [];

		// load buddies connected to the user
		if ($buddies = $this->buddyGateway->listBuddies($userId)) {
			$result = [];
			foreach ($buddies as $b) {
				$img = '/img/avatar-mini.png';

				if (!empty($b['photo'])) {
					$img = $this->imageHelper->img($b['photo']);
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

		// load groups connected to the user
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

		// load stores connected to the user
		if ($betriebe = $this->storeGateway->listMyStores($userId)) {
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

		// load regions connected to the user
		$bezirke = $this->regionGateway->listForFoodsaverExceptWorkingGroups($userId);
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
