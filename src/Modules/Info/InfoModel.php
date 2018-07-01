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
		if ($ids = S::getBotBezirkIds()) {
			return $this->qOne('SELECT COUNT(id) FROM fs_fairteiler WHERE bezirk_id IN(' . implode(',', $ids) . ') AND `status` = 0');
		}
	}
}
