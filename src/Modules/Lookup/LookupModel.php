<?php

namespace Foodsharing\Modules\Lookup;

use Foodsharing\Lib\Db\Db;

class LookupModel extends Db
{
	public function getFoodsaverByEmail($email)
	{
		return $this->qRow("SELECT * FROM fs_foodsaver WHERE email = '" . $this->safe($email) . "'");
	}
}
