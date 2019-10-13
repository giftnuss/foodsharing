<?php

namespace Foodsharing\Modules\Statsman;

use Foodsharing\Modules\Core\BaseGateway;

class StatsmanGateway extends BaseGateway
{
	public function cleanUpTotalFetchQuantities(): void
	{
		$this->db->delete('fs_stat_abholmengen', []);
	}

	public function listStores(): array
	{
		return $this->db->fetchAll('SELECT id FROM fs_betrieb');
	}

	public function insertWeightsToFetchQuantities(int $storeId): void
	{
		$this->db->execute(
			'INSERT INTO fs_stat_abholmengen
						   SELECT b.id, a.`date`, m.weight
						   FROM fs_betrieb b
						   INNER JOIN fs_abholer a ON a.betrieb_id = b.id
						   INNER JOIN fs_abholmengen m ON m.id = b.abholmenge
						   WHERE b.id = ' . $storeId . '
						   GROUP BY a.`date`'
		);
	}
}
