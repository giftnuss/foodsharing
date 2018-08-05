<?php

namespace Foodsharing\Modules\Statistics;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;

class StatisticsGateway extends BaseGateway
{
	public function listTotalStat(): array
	{
		$stm = '
	
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
					`id` = :region_id
		';

		return $this->db->fetch($stm, [':region_id' => RegionIDs::EUROPE]);
	}

	public function listStatCities(): array
	{
		$stm = '
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
				`type` IN(:city, :bigCity)
	
			ORDER BY fetchweight DESC
			LIMIT 10
		';

		return $this->db->fetchAll($stm, [':city' => Type::CITY, ':bigCity' => Type::BIG_CITY]);
	}

	public function listStatFoodsaver(): array
	{
		$stm = '
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
		';

		return $this->db->fetchAll($stm);
	}
}
