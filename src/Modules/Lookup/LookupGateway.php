<?php

namespace Foodsharing\Modules\Lookup;

use Foodsharing\Modules\Core\BaseGateway;

class LookupGateway extends BaseGateway
{
	public function getFoodsaverByEmail($email): array
	{
		$stm = 'SELECT * FROM fs_foodsaver WHERE email = :email';

		return $this->db->fetch($stm, [':email' => $email]);
	}
}
