<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\SearchPermissions;

class SearchHelper
{
	private SearchGateway $searchGateway;
	private RegionGateway $regionGateway;
	private Session $session;
	private SearchPermissions $searchPermissions;

	public function __construct(
		SearchGateway $searchGateway,
		RegionGateway $regionGateway,
		Session $session,
		SearchPermissions $searchPermissions)
	{
		$this->searchGateway = $searchGateway;
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
		if ($this->searchPermissions->maySearchAllRegions()) {
			$regionsFilter = $this->regionGateway->listIdsForDescendantsAndSelf($this->session->getCurrentRegionId());
		}

		$regions = $this->searchGateway->searchRegions($q);
		$users = $this->searchGateway->searchUsers($q, $this->session->may('orga'), $regionsFilter);
		$stores = $this->searchGateway->searchStores($q, $regionsFilter);

		return [
			'regions' => $regions,
			'users' => $users,
			'stores' => $stores
		];
	}
}
