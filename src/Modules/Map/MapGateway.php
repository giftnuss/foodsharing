<?php

namespace Foodsharing\Modules\Map;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;

class MapGateway extends BaseGateway
{
	public function __construct(
		Database $db
	) {
		parent::__construct($db);
	}

	public function getStoreLocation(int $storeId): array
	{
		return $this->db->fetchByCriteria('fs_betrieb', ['lat', 'lon'], ['id' => $storeId]);
	}

	public function getFoodsaverLocation(int $foodsaverId): array
	{
		return $this->db->fetchByCriteria('fs_foodsaver', ['lat', 'lon'], ['id' => $foodsaverId]);
	}
}
