<?php

namespace Foodsharing\Modules\Basket;


use Foodsharing\Modules\Core\BaseGateway;

class BasketGateway extends BaseGateway
{

	public function getUpdateCount($id)
	{
		return (int)$this->db->fetchFirstColumn('
				SELECT COUNT(a.basket_id)
				FROM fs_basket_anfrage a, fs_basket b
				WHERE a.basket_id = b.id
				AND a.`status` = 0
				AND b.foodsaver_id = :foodsaver_id
			',
			[':foodsaver_id' => $id]
		);
	}
}
