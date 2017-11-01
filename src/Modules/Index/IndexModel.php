<?php

namespace Foodsharing\Modules\Index;

use Foodsharing\Modules\Core\Model;

class IndexModel extends Model
{
	public function getGerettet()
	{
		return $this->qOne('SELECT stat_fetchweight FROM ' . PREFIX . 'bezirk WHERE id = 741');
	}
}
