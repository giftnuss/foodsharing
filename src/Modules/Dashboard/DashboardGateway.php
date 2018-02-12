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

	public function getNewestFoodbaskets($limit = 10)
	{
		return $this->db->fetchAll('
	
			SELECT
				b.id,
				b.`time`,
				UNIX_TIMESTAMP(b.`time`) AS time_ts,
				b.description,
				b.picture,
				b.contact_type,
				b.tel,
				b.handy,
				b.fs_id AS fsf_id,
				fs.id AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo
	
			FROM
				fs_basket b,
				fs_foodsaver fs
	
			WHERE
				b.foodsaver_id = fs.id
			AND
				b.status = 1
	
			ORDER BY
				id DESC
	
			LIMIT
				0, :limit
	
		', [':limit' => $limit]);
	}

	public function listCloseBaskets($id, $loc, $distance = 50)
	{
		return $this->db->fetchAll('
			SELECT
				b.id,
				b.time,
				UNIX_TIMESTAMP(b.`time`) AS time_ts,
				b.picture,
				b.description,
				b.lat,
				b.lon,
				(6371 * acos( cos( radians(:lat) ) * cos( radians( b.lat ) ) * cos( radians( b.lon ) - radians(:lon) ) + sin( radians(:lat) ) * sin( radians( b.lat ) ) ))
				AS distance,
				fs.name AS fs_name
			FROM
				fs_basket b,
				fs_foodsaver fs
	
			WHERE
				b.foodsaver_id = fs.id
				
			AND
				b.status = 1
	
			AND
				foodsaver_id != :id
		
			HAVING
				distance <= :distance
	
			ORDER BY
				distance ASC
	
			LIMIT 6
		', [':id' => $id, ':distance' => $distance, ':lat' => $loc['lat'], ':lon' => $loc['lon']]);
	}
}
