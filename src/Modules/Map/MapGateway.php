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

	public function getBasketMarkers(): array
	{
		return $this->db->fetchAllByCriteria('fs_basket', ['id', 'lat', 'lon', 'location_type'], [
			'status' => 1
		]);
	}

	public function getFoodSharePointMarkers(): array
	{
		return $this->db->fetchAllByCriteria('fs_fairteiler', ['id', 'lat', 'lon', 'bezirk_id'], [
			'status' => 1,
			'lat !=' => ''
		]);
	}

	public function getStoreMarkers(array $excludedStoreTypes, array $teamStatus): array
	{
		$query = 'SELECT id, lat, lon FROM fs_betrieb WHERE lat != ""';

		if (!empty($excludedStoreTypes)) {
			$query .= ' AND betrieb_status_id NOT IN(' . implode(',', $excludedStoreTypes) . ')';
		}
		if (!empty($teamStatus)) {
			$query .= ' AND team_status IN (' . implode(',', $teamStatus) . ')';
		}

		return $this->db->fetchAll($query);
	}
}
