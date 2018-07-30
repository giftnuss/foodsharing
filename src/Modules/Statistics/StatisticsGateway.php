<?php

namespace Foodsharing\Modules\Statistics;

use Foodsharing\Modules\Core\BaseGateway;

class StatisticsGateway extends BaseGateway
{
	public function getStatGesamt()
	{
		return $this->db->fetch('
	
				SELECT
					SUM(`stat_fetchweight`) AS fetchweight,
					SUM(`stat_fetchcount`) AS fetchcount,
					SUM(`stat_postcount`) AS postcount,
					SUM(`stat_betriebcount`) AS betriebcount,
					SUM(`stat_korpcount`) AS korpcount,
					SUM(`stat_botcount`) AS botcount,
					SUM(`stat_fscount`) AS fscount,
					SUM(`stat_fairteilercount`) AS fairteilercount
	
				FROM
					fs_bezirk
	
				WHERE
					`id` = 741
		');
	}

	public function getStatCities()
	{
		return $this->db->fetchAll('
			SELECT
				`id`,
				`name`,
				`stat_fetchweight` AS fetchweight,
				`stat_fetchcount` AS fetchcount,
				`stat_postcount`AS postcount,
				`stat_betriebcount` AS betriebcount,
				`stat_korpcount` AS korpcount,
				`stat_botcount` AS botcount,
				`stat_fscount` AS fscount,
				`stat_fairteilercount` AS fairteilercount
			FROM
				fs_bezirk
	
			WHERE
				`type` IN(1,8)
	
			ORDER BY fetchweight DESC
			LIMIT 10
		');
	}

	public function getStatFoodsaver()
	{
		return $this->db->fetchAll('
			SELECT
				`id`,
				`name`,
				`nachname`,
				`stat_fetchweight` AS fetchweight,
				`stat_fetchcount` AS fetchcount
			FROM
				fs_foodsaver
			WHERE
				deleted_at IS NULL
	
			ORDER BY fetchweight DESC
			LIMIT 10
		');
	}
}
