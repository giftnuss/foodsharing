<?php

namespace Foodsharing\Modules\Info;

use Foodsharing\Modules\Core\Model;

class InfoModel extends Model
{
	/**
	 * returns the count of new fairteiler.
	 */
	public function getFairteilerBadgdeCount()
	{
		if ($ids = $this->getBotBezirkIds()) {
			return $this->qOne('SELECT COUNT(id) FROM ' . PREFIX . 'fairteiler WHERE bezirk_id IN(' . implode(',', $ids) . ') AND `status` = 0');
		}
	}
}
