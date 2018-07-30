<?php

namespace Foodsharing\Modules\Lookup;

use Foodsharing\Modules\Core\Model;

class LookupModel extends Model
{
	public function getFoodsaverByEmail($email)
	{
		return $this->qRow("SELECT * FROM fs_foodsaver WHERE email = '" . $this->safe($email) . "'");
	}
}
