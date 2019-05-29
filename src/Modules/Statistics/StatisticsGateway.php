<?php

namespace Foodsharing\Modules\Statistics;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use DateTime;

class StatisticsGateway extends BaseGateway
{
	public function listTotalStat(): array
	{
		$stm = '	
				SELECT
					SUM(`stat_fetchweight`) AS fetchweight,
					SUM(`stat_fetchcount`) AS fetchcount,
					SUM(`stat_korpcount`) AS cooperationscount,
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
				`name`,
				`stat_fetchweight` AS fetchweight,
				`stat_fetchcount` AS fetchcount,
				`type`
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

	public function countAllFoodsharers(): int
	{
		return $this->db->count('fs_foodsaver', ['active' => 1, 'deleted_at' => null]);
	}

	public function avgDailyFetchCount(): int
	{
		// get number of all fetches
		$q = '
	    SELECT
	      SUM(`stat_fetchcount`) AS fetchcount
	    FROM
	      fs_bezirk
	    WHERE
	      `id` = :region_id
	  ';
		$fetchcount = (int)$this->db->fetch($q, [':region_id' => RegionIDs::EUROPE])['fetchcount'];
		// Get todays date
		$date = new DateTime(date('Y-m-d'));
		// get first fetch date
		$q = '
	    SELECT MIN(`date`) as mindate from fs_abholer
	  ';
		$startdate = new DateTime($this->db->fetch($q)['mindate']);
		// difference between days and fetches
		$diffdays = $startdate->diff($date)->days;
		// divide number of fetches by time difference
		return (int)$fetchcount / $diffdays;
	}
}
