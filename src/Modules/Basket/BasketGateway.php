<?php

namespace Foodsharing\Modules\Basket;

use Foodsharing\Modules\Core\BaseGateway;

class BasketGateway extends BaseGateway
{
	public function getUpdateCount($id): int
	{
		$stm = '
				SELECT COUNT(a.basket_id)
				FROM fs_basket_anfrage a, fs_basket b
				WHERE a.basket_id = b.id
				AND a.`status` = 0
				AND b.foodsaver_id = :foodsaver_id
			';

		return (int)$this->db->fetchValue($stm,	[':foodsaver_id' => $id]);
	}

	public function getBasketCoordinates(): array
	{
		$stm = '
			SELECT id,lat,lon 
			FROM fs_basket 
			WHERE status = 1
			';

		return $this->db->fetchAll($stm);
	}

	public function listRequests($basket_id, $id): array
	{
		$stm = '		
				SELECT
					UNIX_TIMESTAMP(a.time) AS time_ts,
					fs.name AS fs_name,
					fs.photo AS fs_photo,
					fs.id AS fs_id,
					fs.geschlecht AS fs_gender,
					fs.sleep_status,
					b.id		
		
				FROM
					fs_basket_anfrage a,
					fs_basket b,
					fs_foodsaver fs
		
				WHERE
					a.basket_id = b.id
		
				AND
					a.`status` IN(0,1)
		
				AND
					a.foodsaver_id = fs.id
		
				AND
					b.foodsaver_id = :foodsaver_id
		
				AND
					a.basket_id = :basket_id		
				';

		return $this->db->fetchAll($stm, [':foodsaver_id' => $id, ':basket_id' => $basket_id]);
	}

	public function getRequest($basket_id, $fs_id, $id)
	{
		$stm = '		
				SELECT
					UNIX_TIMESTAMP(a.time) AS time_ts,
					fs.name AS fs_name,
					fs.photo AS fs_photo,
					fs.id AS fs_id,
					fs.geschlecht AS fs_gender,
					b.id		
		
				FROM
					fs_basket_anfrage a,
					fs_basket b,
					fs_foodsaver fs
		
				WHERE
					a.basket_id = b.id
		
				AND
					a.`status` IN(0,1)
		
				AND
					a.foodsaver_id = fs.id
		
				AND
					b.foodsaver_id = :foodsaver_id
				
				AND
					a.foodsaver_id = :fs_id
				
				AND
					a.basket_id = :basket_id		
				';

		return $this->db->fetch($stm, [':foodsaver_id' => $id, ':fs_id' => $fs_id, ':basket_id' => $basket_id]);
	}

	public function listUpdates($fsId): array
	{
		$stm = '
			SELECT 
				UNIX_TIMESTAMP(a.time) AS time_ts,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				fs.id AS fs_id,
				fs.sleep_status,
				b.id,
				b.description				
				
			FROM 
				fs_basket_anfrage a, 
				fs_basket b,
				fs_foodsaver fs
				
			WHERE 
				a.basket_id = b.id 
				
			AND 
				a.`status` IN(0,1)
				
			AND
				a.foodsaver_id = fs.id
				
			AND
				b.foodsaver_id = :foodsaver_id
				
			ORDER BY
				a.`time` DESC				
		';

		return $this->db->fetchAll($stm, [':foodsaver_id' => $fsId]);
	}

	public function removeBasket($id, $fsId): int
	{
		return $this->db->update('fs_basket',['status' => 3], ['id' => (int)$id, 'foodsaver_id' => $fsId]);
	}
}
