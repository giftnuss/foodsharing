<?php

namespace Foodsharing\Modules\Maintenance;

use Foodsharing\Lib\Db\Db;

class MaintenanceModel extends Db
{
	public function getStoreManagersWhichWillBeAlerted()
	{
		$dow = (int)date('w');
		$dow_tomorrow = ($dow + 1) % 7;

		$store_query = '
			SELECT 
				DISTINCT z.betrieb_id

			FROM 
				fs_abholzeiten z
				
			LEFT JOIN
				fs_betrieb b
				
			ON
				z.betrieb_id = b.id
				
			WHERE
				b.betrieb_status_id IN(3,5)
				
			AND
			(
				(
					z.dow = ' . (int)$dow . '
					AND
					z.time >= "' . date('H:i:s') . '"
				)
				OR
					z.dow = ' . (int)$dow_tomorrow . '
			)
		';

		if ($stores_in_range = $this->q($store_query)) {
			$bids = [];

			foreach ($stores_in_range as $store) {
				$bids[(int)$store['betrieb_id']] = (int)$store['betrieb_id'];
			}

			$fetcher_query = '
				SELECT
					DISTINCT a.betrieb_id AS id
				FROM
					fs_abholer a
				WHERE 
					a.confirmed = 1
				AND 
					a.betrieb_id IN(' . implode(',', $bids) . ')
				AND
					a.date >= NOW()
				AND
					a.date <= CURRENT_DATE() + INTERVAL 2 DAY
			';

			if ($store_has_fetcher = $this->q($fetcher_query)) {
				foreach ($store_has_fetcher as $store_fetcher) {
					unset($bids[$store_fetcher['id']]);
				}
			}

			if (!empty($bids)) {
				return $this->q('
					SELECT
						fs.id AS fs_id,
						fs.email AS fs_email,
						fs.geschlecht,
						fs.name AS fs_name,
						b.id AS betrieb_id,
						b.name AS betrieb_name
						
					FROM
						fs_betrieb b,
						fs_betrieb_team bt,
						fs_foodsaver fs
						
					WHERE
						b.id = bt.betrieb_id
						
					AND
						bt.foodsaver_id = fs.id
						
					AND
						bt.active = 1
						
					AND
						bt.verantwortlich = 1
					
					AND
						b.id IN(' . implode(',', $bids) . ')');
			}
		}

		return false;
	}

	public function deleteOldIpBlocks()
	{
		return $this->del('DELETE FROM `fs_ipblock` WHERE UNIX_TIMESTAMP(NOW()) > UNIX_TIMESTAMP(start)+duration ');
	}
}
