<?php

namespace Foodsharing\Modules\Maintenance;

use Foodsharing\Modules\Core\BaseGateway;

class MaintenanceGateway extends BaseGateway
{
	public function deactivateOldBaskets(): int
	{
		return $this->db->update(
			'fs_basket',
			['status' => 6],
			['status' => 1, 'until <' => $this->db->now()]);
	}

	public function deleteUnconfirmedFetchDates(): int
	{
		return $this->db->delete('fs_abholer', ['confirmed' => 0, 'date <' => $this->db->now()]);
	}

	public function wakeupSleepingUsers()
	{
		return $this->db->update(
			'fs_foodsaver',
			['sleep_status' => 0],
			['sleep_status' => 1, 'sleep_until >' => 0, 'sleep_until <' => $this->db->now()]);
	}

	/**
	 * Returns the managers for all stores that have pickup free slots in the next two days.
	 */
	public function getStoreManagersWhichWillBeAlerted(): array
	{
		$dow = (int)date('w');
		$dowTomorrow = ($dow + 1) % 7;

		// find all stores with pickup slots today or tomorrow
		$storesInRange = $this->db->fetchAll('
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
					z.dow = :dow
					AND
					z.time >= :time
				)
				OR
					z.dow = :dowTomorrow
			)
		', [
			':dow' => $dow,
			':time' => date('H:i:s'),
			':dowTomorrow' => $dowTomorrow
		]);

		if (!empty($storesInRange)) {
			$storeIds = [];
			foreach ($storesInRange as $store) {
				$storeIds[(int)$store['betrieb_id']] = (int)$store['betrieb_id'];
			}

			// remove all stores from the list that have someone who will pickup
			$storeWithFetcher = $this->db->fetchAll('
				SELECT
					DISTINCT a.betrieb_id AS id
				FROM
					fs_abholer a
				WHERE
					a.confirmed = 1
				AND
					a.betrieb_id IN(:storeIds)
				AND
					a.date >= NOW()
				AND
					a.date <= CURRENT_DATE() + INTERVAL 2 DAY
			', [
				':storeIds' => implode(',', $storeIds)
			]);

			foreach ($storeWithFetcher as $s) {
				unset($storeIds[$s['id']]);
			}

			// return the managers for all remaining stores in the list
			if (!empty($storeIds)) {
				return $this->db->fetchAll('
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
						b.id IN(:storeIds)', [
						':storeIds' => implode(',', $storeIds)
					]);
			}
		}

		return [];
	}

	public function deleteOldIpBlocks()
	{
		return $this->db->execute('DELETE FROM `fs_ipblock` WHERE UNIX_TIMESTAMP(NOW()) > UNIX_TIMESTAMP(start)+duration ');
	}
}
