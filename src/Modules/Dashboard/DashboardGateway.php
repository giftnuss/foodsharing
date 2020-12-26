<?php

namespace Foodsharing\Modules\Dashboard;

use Foodsharing\Modules\Core\BaseGateway;

class DashboardGateway extends BaseGateway
{
	public function getUser($id)
	{
		return $this->db->fetch('
				SELECT
					`id`,
					`name`,
					rolle,
					TIMESTAMP(last_login) AS last_login_ts,
					sleep_status,
					photo,
					stat_fetchweight,
					lat,
					lon
				FROM fs_foodsaver
				WHERE id = :id
			', [':id' => $id]);
	}

	/**
	 * Returns if any store from the list of store IDs is not assigned to a valid district.
	 */
	public function hasStoresWithoutDistrict(array $storeIds): bool
	{
		return $this->db->exists('fs_betrieb', [
			'id' => $storeIds,
			'bezirk_id' => 0,
		]) || $this->db->exists('fs_betrieb', [
			'id' => $storeIds,
			'bezirk_id' => null,
		]);
	}
}
