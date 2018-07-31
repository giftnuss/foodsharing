<?php

namespace Foodsharing\Modules\Index;

use Foodsharing\Lib\Db\Db;

class IndexModel extends Db
{
	public function getGerettet()
	{
		return $this->qOne('SELECT stat_fetchweight FROM fs_bezirk WHERE id = 741');
	}
}
