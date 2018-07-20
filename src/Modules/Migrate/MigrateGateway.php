<?php

namespace Foodsharing\Modules\Migrate;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;

class MigrateGateway extends BaseGateway
{
	public function __construct(Database $db)
	{
		parent::__construct($db);
	}

	public function forumPostsRemoveBr($date = null)
	{
		/* forum posts have been treated with nl2br which adds a '<br />' in front of each nl. */
		$q = 'UPDATE fs_theme_post SET body = REPLACE (body, "<br />", "")';
		if ($date) {
			$q .=
				' WHERE `time` < :time';

			return $this->db->execute($q, ['time' => $date])->rowCount();
		} else {
			return $this->db->execute($q)->rowCount();
		}
	}
}
