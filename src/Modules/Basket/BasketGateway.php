<?php

namespace Foodsharing\Modules\Basket;

use Foodsharing\Modules\Core\BaseGateway;

class BasketGateway extends BaseGateway
{
	public function getUpdateCount($id)
	{
		return (int)$this->db->fetchValue('
				SELECT COUNT(a.basket_id)
				FROM fs_basket_anfrage a, fs_basket b
				WHERE a.basket_id = b.id
				AND a.`status` = 0
				AND b.foodsaver_id = :foodsaver_id
			',
			[':foodsaver_id' => $id]
		);
	}

	public function listCloseBaskets($fs_id, $loc, $distance = 30)
	{
		return $this->db->fetchAll('
			SELECT
				b.id,
				b.picture,
				b.description,
				b.lat,
				b.lon,
				(6371 * acos( cos( radians( :lat ) ) * cos( radians( b.lat ) ) * cos( radians( b.lon ) - radians( :lon ) ) + sin( radians( :lat ) ) * sin( radians( b.lat ) ) ))
				AS distance
			FROM
				fs_basket b

			WHERE
				b.status = 1

			AND
				foodsaver_id != :fs_id

			HAVING
				distance <= :distance

			ORDER BY
				distance ASC

			LIMIT 6
		', [
			':lat' => (float)$loc['lat'],
			':lon' => (float)$loc['lon'],
			':fs_id' => $fs_id,
			':distance' => $distance,
		]);
	}
}
