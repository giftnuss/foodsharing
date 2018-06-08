<?php

namespace Foodsharing\Modules\API;

use Foodsharing\Modules\Core\BaseGateway;

class APIGateway extends BaseGateway
{
	public function getOrgaGroups(): array
	{
		$stm = 'SELECT id, name, parent_id FROM fs_bezirk WHERE type = 7 ORDER BY parent_id';

		return $this->db->fetchAll($stm);
	}

	public function allBaskets(): array
	{
		$stm = '
			SELECT
				b.id AS i,
				b.lat AS a,
				b.lon AS o
			FROM
				fs_basket b
		
			WHERE
				b.status = 1
		
			AND
				b.fs_id = 0

		';

		return $this->db->fetchAll($stm);
	}

	public function nearBaskets($lat, $lon, $distance = 50): array
	{
		$stm = '
			SELECT 	
				b.id AS i,
				b.lat AS a, 
				b.lon AS o, 
				(6371 * acos( cos( radians( ' . (float)$lat . ' ) ) * cos( radians( b.lat ) ) * cos( radians( b.lon ) - radians( ' . (float)$lon . ' ) ) + sin( radians( ' . (float)$lat . ' ) ) * sin( radians( b.lat ) ) ))
				AS d
			FROM 	
				fs_basket b
				
			WHERE
				b.status = 1
				
			AND
				b.fs_id = 0
				
			HAVING 
				d <=' . (int)$distance . '
		';

		return $this->db->fetchAll($stm);
	}

	public function getBasket($id)
	{
		$stm = '
				SELECT
					b.id,
					b.description,
					b.picture,
					b.contact_type,
					b.tel,
					b.handy,
					b.fs_id AS fsf_id,
					b.foodsaver_id,
					b.lat,
					b.lon
	
				FROM
					fs_basket b
	
				WHERE
					b.id = ' . (int)$id . '
		';
		$basket = $this->db->fetch($stm);

		$stm = '
				SELECT
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				fs.id AS fs_id
						
				FROM
				fs_foodsaver fs
						
				WHERE
				fs.id = ' . (int)$basket['foodsaver_id'] . '						
			';
		if ($basket['fsf_id'] == 0 && $fs = $this->db->fetch($stm)) {
			$basket = array_merge($basket, $fs);
		}

		return $basket;
	}
}
