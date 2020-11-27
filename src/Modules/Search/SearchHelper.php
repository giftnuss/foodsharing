<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\SearchPermissions;

class SearchHelper
{
	private SearchGateway $searchGateway;
	private FoodsaverGateway $foodsaverGateway;
	private RegionGateway $regionGateway;
	private Session $session;
	private SearchPermissions $searchPermissions;

	public function __construct(
		SearchGateway $searchGateway,
		FoodsaverGateway $foodsaverGateway,
		RegionGateway $regionGateway,
		Session $session,
		SearchPermissions $searchPermissions
	) {
		$this->searchGateway = $searchGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionGateway = $regionGateway;
		$this->session = $session;
		$this->searchPermissions = $searchPermissions;
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
}
