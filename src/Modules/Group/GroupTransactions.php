<?php

namespace Foodsharing\Modules\Group;

use Foodsharing\Modules\FoodSharePoint\FoodSharePointGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;

class GroupTransactions
{
	private RegionGateway $regionGateway;
	private FoodSharePointGateway $foodSharePointGateway;
	private StoreGateway $storeGateway;

	public function __construct(
		RegionGateway $regionGateway,
		FoodSharePointGateway $foodSharePointGateway,
		StoreGateway $storeGateway
	) {
		$this->regionGateway = $regionGateway;
		$this->foodSharePointGateway = $foodSharePointGateway;
		$this->storeGateway = $storeGateway;
	}

	/**
	 * Returns whether the group still contains any sub-regions, stores, or food-share-points.
	 */
	public function hasSubElements(int $groupId): bool
	{
		$regions = $this->regionGateway->getBezirkByParent($groupId, true);
		if (!empty($regions)) {
			return true;
		}

		$fsps = $this->foodSharePointGateway->listFoodSharePointsNested([$groupId]);
		if (!empty($fsps)) {
			return true;
		}

		$stores = $this->storeGateway->listStoresInRegion($groupId, true);
		if (!empty($stores)) {
			return true;
		}

		return false;
	}
}
