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
	 * Returns the number of stores from the list of store IDs that are not assigned to a district.
	 *
	 * @param $storeIds
	 */
	public function countStoresWithoutDistrict($storeIds): int
	{
		return (int)$this->db->fetchValue('SELECT COUNT(*) FROM fs_betrieb WHERE id IN('
			. implode(',', $storeIds)
			. ') AND ( bezirk_id = 0 OR bezirk_id IS NULL)');
	}
}
