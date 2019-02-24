<?php

namespace Foodsharing\Services;

use Foodsharing\Lib\Func;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Buddy\BuddyGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreModel;
use Foodsharing\Modules\WorkGroup\WorkGroupModel;

class SearchService
{
	private $buddyGateway;
	private $workGroupModel;
	private $storeModel;
	private $regionGateway;
	private $func;
	private $session;
	private $sanitizerService;

	public function __construct(
		BuddyGateway $buddyGateway,
		WorkGroupModel $workGroupModel,
		StoreModel $storeModel,
		regionGateway $regionGateway,
		Func $func,
		Session $session,
		SanitizerService $sanitizerService
	) {
		$this->buddyGateway = $buddyGateway;
		$this->workGroupModel = $workGroupModel;
		$this->storeModel = $storeModel;
		$this->regionGateway = $regionGateway;
		$this->func = $func;
		$this->session = $session;
		$this->sanitizerService = $sanitizerService;
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
					$img = $this->func->img($b['photo']);
				}

				$result[] = array(
					'name' => $b['name'] . ' ' . $b['nachname'],
					'teaser' => '',
					'img' => $img,
					'click' => 'chat(\'' . $b['id'] . '\');',
					'id' => $b['id'],
					'search' => array(
						$b['name'], $b['nachname']
					)
				);
			}
			$index[] = array(
				'title' => 'Menschen die Du kennst',
				'key' => 'buddies',
				'result' => $result
			);
		}

		/*
		 * Groups load Groups connected to the user in the array
		*/
		if ($groups = $this->workGroupModel->listMemberGroups($fsId)) {
			$result = [];
			foreach ($groups as $b) {
				$img = '/img/groups.png';
				if (!empty($b['photo'])) {
					$img = 'images/' . str_replace('photo/', 'photo/thumb_', $b['photo']);
				}
				$result[] = array(
					'name' => $b['name'],
					'teaser' => $this->sanitizerService->tt($b['teaser'], 65),
					'img' => $img,
					'href' => '/?page=bezirk&bid=' . $b['id'] . '&sub=forum',
					'search' => array(
						$b['name']
					)
				);
			}
			$index[] = array(
				'title' => 'Deine Gruppen',
				'result' => $result
			);
		}

		/*
		 * Betriebe load food stores connected to the user in the array
		*/
		if ($betriebe = $this->storeModel->listMyBetriebe()) {
			$result = [];
			foreach ($betriebe as $b) {
				$result[] = array(
					'name' => $b['name'],
					'teaser' => $b['str'] . ' ' . $b['hsnr'] . ', ' . $b['plz'] . ' ' . $b['stadt'],
					'href' => '/?page=fsbetrieb&id=' . $b['id'],
					'search' => array(
						$b['name'], $b['str']
					)
				);
			}
			$index[] = array(
				'title' => 'Deine Betriebe',
				'result' => $result
			);
		}

		/*
		 * Bezirke load Bezirke connected to the user in the array
		*/
		$bezirke = $this->regionGateway->listForFoodsaverExceptWorkingGroups($this->session->id());
		$result = [];
		foreach ($bezirke as $b) {
			$result[] = array(
				'name' => $b['name'],
				'teaser' => '',
				'img' => false,
				'href' => '/?page=bezirk&bid=' . $b['id'] . '&sub=forum',
				'search' => array(
					$b['name']
				)
			);
		}
		$index[] = array(
			'title' => 'Deine Bezirke',
			'result' => $result
		);

		return $index;
	}
}
