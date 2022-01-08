<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Buddy\BuddyGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Search\DTO\SearchIndexEntry;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\WorkGroup\WorkGroupGateway;
use Foodsharing\Permissions\SearchPermissions;
use Foodsharing\Utility\ImageHelper;
use Foodsharing\Utility\Sanitizer;

class SearchTransactions
{
	private SearchGateway $searchGateway;
	private FoodsaverGateway $foodsaverGateway;
	private RegionGateway $regionGateway;
	private StoreGateway $storeGateway;
	private BuddyGateway $buddyGateway;
	private WorkGroupGateway $workGroupGateway;
	private Session $session;
	private SearchPermissions $searchPermissions;
	private Sanitizer $sanitizerService;
	private ImageHelper $imageHelper;

	public function __construct(
		SearchGateway $searchGateway,
		FoodsaverGateway $foodsaverGateway,
		RegionGateway $regionGateway,
		BuddyGateway $buddyGateway,
		WorkGroupGateway $workGroupGateway,
		StoreGateway $storeGateway,
		Session $session,
		SearchPermissions $searchPermissions,
		Sanitizer $sanitizerService,
		ImageHelper $imageHelper
	) {
		$this->searchGateway = $searchGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionGateway = $regionGateway;
		$this->buddyGateway = $buddyGateway;
		$this->workGroupGateway = $workGroupGateway;
		$this->storeGateway = $storeGateway;
		$this->session = $session;
		$this->searchPermissions = $searchPermissions;
		$this->sanitizerService = $sanitizerService;
		$this->imageHelper = $imageHelper;
	}

	/**
	 * Searches for regions, stores, and foodsavers.
	 *
	 * @param string $q the search query
	 *
	 * @return array SearchResult[]
	 */
	public function search(string $q): array
	{
		$regionsFilter = null;
		if (!$this->searchPermissions->maySearchAllRegions()) {
			$regionsFilter = $this->regionGateway->listIdsForDescendantsAndSelf($this->session->getCurrentRegionId());
		}

		$regions = $this->searchGateway->searchRegions($q);
		$users = $this->searchGateway->searchUserInGroups($q, $this->searchPermissions->maySeeUserAddress(), $regionsFilter);
		$stores = $this->searchGateway->searchStores($q, $regionsFilter);
		if ($singleUser = $this->searchSingleUserByID($q)) {
			array_unshift($users, $singleUser);
		}

		return [
			'regions' => $regions,
			'users' => $users,
			'stores' => $stores
		];
	}

	private function searchSingleUserByID(string $q): array
	{
		if (!preg_match('/^[0-9]+$/', $q)) {
			return [];
		}
		$userId = intval($q);

		if (!$this->foodsaverGateway->foodsaverExists($userId)) {
			return [];
		}

		return [
			'id' => $userId,
			'name' => $this->foodsaverGateway->getFoodsaverName($userId),
			'teaser' => 'FS-ID: ' . $userId,
		];
	}

	/**
	 * Generates the search index for instant search. Each category (stores, regions, buddies, groups)
	 * is mapped to a list of {@link SearchIndexEntry}s.
	 */
	public function generateIndex(): array
	{
		$userId = $this->session->id();
		$index = [];

		// load buddies of the user
		if ($buddies = $this->buddyGateway->listBuddies($userId)) {
			$index['myBuddies'] = array_map(function ($b) {
				$img = '/img/avatar-mini.png';
				if (!empty($b['photo'])) {
					$img = $this->imageHelper->img($b['photo']);
				}

				return SearchIndexEntry::create($b['id'], $b['name'] . ' ' . $b['nachname'], null, $img);
			}, $buddies);
		}

		// load groups in which the user is a member
		if ($groups = $this->workGroupGateway->listMemberGroups($userId)) {
			$index['myGroups'] = array_map(function ($b) {
				$img = '/img/groups.png';
				if (!empty($b['photo'])) {
					$img = 'images/' . str_replace('photo/', 'photo/thumb_', $b['photo']);
				}

				return SearchIndexEntry::create($b['id'], $b['name'], $this->sanitizerService->tt($b['teaser'], 65), $img);
			}, $groups);
		}

		// load stores in which the user is a member
		if ($betriebe = $this->storeGateway->listMyStores($userId)) {
			$index['myStores'] = array_map(function ($b) {
				return SearchIndexEntry::create($b['id'], $b['name'], $b['str'] . ' ' . $b['hsnr'] . ', ' . $b['plz'] . ' ' . $b['stadt'], null);
			}, $betriebe);
		}

		// load regions in which the user is a member
		$bezirke = $this->regionGateway->listForFoodsaverExceptWorkingGroups($userId);
		$index['myRegions'] = array_map(function ($b) {
			return SearchIndexEntry::create($b['id'], $b['name'], null, null);
		}, $bezirke);

		return $index;
	}
}
