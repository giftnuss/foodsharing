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
}
