<?php

namespace Foodsharing\Modules\Stats;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;

class StatsGateway extends BaseGateway
{
	private StatsService $statsService;

	public function __construct(Database $db, StatsService $statsService)
	{
		$this->statsService = $statsService;

		parent::__construct($db);
	}

	public function fetchAllStores(): array
	{
		return $this->db->fetchAll('SELECT id, name, added FROM fs_betrieb');
	}

	/**
	 * Update the number of pickups, and the first and last pickup for each user in the store.
	 *
	 * @param int $storeId the store that will be updated
	 *
	 * @throws \Exception
	 */
	public function updateStoreUsersData(int $storeId): void
	{
		$this->db->fetch('UPDATE fs_betrieb_team,
				(SELECT bt.foodsaver_id,
				        IFNULL(innerstat.fetchcount, 0) as fetchcount,
				        IFNULL(innerstat.last_fetch, null) as last_fetch,
				        IFNULL(innerstat.first_fetch, null) as first_fetch
				FROM fs_betrieb_team bt
				LEFT OUTER JOIN (
				    SELECT a.foodsaver_id,
				           COUNT(*) as fetchcount,
				           DATE_FORMAT(max(a.date),"%Y-%m-%d") as last_fetch,
				           DATE_FORMAT(min(a.date),"%Y-%m-%d") as first_fetch
				    FROM fs_abholer a
				    WHERE a.betrieb_id = :storeId
				    AND a.date < NOW()
				    AND a.confirmed = 1
				    GROUP BY a.foodsaver_id) innerstat
				ON bt.foodsaver_id = innerstat.foodsaver_id
				WHERE betrieb_id = :storeId
			) AS storestats
			SET stat_fetchcount = storestats.fetchcount,
			    stat_first_fetch = storestats.first_fetch,
			    stat_last_fetch = storestats.last_fetch
			WHERE storestats.foodsaver_id = fs_betrieb_team.foodsaver_id
			AND fs_betrieb_team.betrieb_id = :storeId', [
			':storeId' => $storeId,
		]);
	}
}
