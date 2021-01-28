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
	 * Returns the number of pickups since the last update for each user in the store who had at least
	 * one pickup in that time. This is returned as an associative array `[foodsaver_id => count]`.
	 */
	public function getStoreUsersFetchCount(int $storeId): array
	{
		$data = $this->db->fetch(
			'SELECT a.foodsaver_id, COUNT(*) as count
			FROM 	fs_abholer a
            LEFT JOIN fs_betrieb_team t
            ON a.foodsaver_id = t.foodsaver_id
			WHERE 	a.betrieb_id = :storeId
            AND (t.stat_last_update IS NULL OR a.date > t.stat_last_update)
			AND 	a.date < NOW()
			AND 	a.confirmed = 1
			GROUP BY a.foodsaver_id', [
			':storeId' => $storeId,
		]);

		return $this->flattenArray($data, 'foodsaver_id', 'count');
	}

	/**
	 * Converts a 2d array `[[key => x, value => y]]` into an associative 1d array `[x => y]`.
	 */
	private function flattenArray(array $data, string $key, string $value)
	{
		$flat = [];
		foreach ($data as $d) {
			$flat[$d[$key]] = $d[$value];
		}

		return $flat;
	}
}
