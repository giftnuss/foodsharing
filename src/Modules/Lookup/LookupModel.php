<?php

namespace Foodsharing\Modules\Lookup;

use Foodsharing\Modules\Console\ConsoleModel;

class LookupModel extends ConsoleModel
{
	public function getFoodsaverByEmail($email)
	{
		return $this->qRow("SELECT * FROM fs_foodsaver WHERE email = '" . $this->safe($email) . "'");
	}
}
