<?php

namespace Foodsharing\Modules\Maintenance;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\DBConstants\Basket\Status;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\SleepStatus;

class MaintenanceGateway extends BaseGateway
{
	/**
	 * Sets the status of all outdated baskets to {@link Status::DELETED_OTHER_REASON}.
	 *
	 * @return int the number of changed baskets
	 */
	public function deactivateOldBaskets(): int
	{
		return $this->db->update(
			'fs_basket',
			['status' => Status::DELETED_OTHER_REASON],
			['status' => Status::REQUESTED_MESSAGE_READ, 'until <' => $this->db->now()]);
	}

	/**
	 * Deletes all unconfirmed fetch dates in the past.
	 *
	 * @return int the number of deleted entries
	 */
	public function deleteUnconfirmedFetchDates(): int
	{
		return $this->db->delete('fs_abholer', ['confirmed' => 0, 'date <' => $this->db->now()]);
	}

	/**
	 * Removes the temporary sleep status from users if it is outdated.
	 *
	 * @return int the number of users that were changed
	 */
	public function wakeupSleepingUsers()
	{
		return $this->db->update(
			'fs_foodsaver',
			['sleep_status' => SleepStatus::NONE],
			['sleep_status' => SleepStatus::TEMP, 'sleep_until >' => 0, 'sleep_until <' => $this->db->now()]);
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

	/**
	 * Deletes all outdated entries from the blocked IPs.
	 *
	 * @return int the number of deleted entries
	 */
	public function deleteOldIpBlocks(): int
	{
		return $this->db->execute('DELETE FROM `fs_ipblock` WHERE UNIX_TIMESTAMP(NOW()) > UNIX_TIMESTAMP(start)+duration ')->rowCount();
	}

	/**
	 * Rebuilds the 'fs_bezirk_closure' table.
	 */
	public function rebuildRegionClosure(): void
	{
		$this->db->beginTransaction();
		$this->db->execute('DELETE FROM fs_bezirk_closure');
		$this->db->execute('
			INSERT INTO fs_bezirk_closure (bezirk_id, ancestor_id, depth)
			SELECT a.id, a.id, 0
			FROM fs_bezirk
			AS a
			WHERE a.parent_id > 0'
		);
		for ($depth = 0; $depth < 6; ++$depth) {
			$this->db->execute('
				INSERT INTO fs_bezirk_closure (bezirk_id, ancestor_id, depth)
				SELECT a.bezirk_id, b.parent_id, a.depth+1
				FROM fs_bezirk_closure
				AS a
				JOIN fs_bezirk
				AS b
				ON b.id = a.ancestor_id
				WHERE b.parent_id IS NOT NULL
				AND a.depth = ' . $depth
			);
		}
		$this->db->commit();
	}

	/**
	 * Makes sure that all foodsavers in regions that have master regions are also members of the master region.
	 */
	public function masterRegionUpdate(): void
	{
		$foodsaver = $this->db->fetchAll('
				SELECT
				b.`id`,
				b.`name`,
				b.`type`,
				b.`master`,
				hb.foodsaver_id

				FROM 	`fs_bezirk` b,
				`fs_foodsaver_has_bezirk` hb

				WHERE 	hb.bezirk_id = b.id
				AND 	b.`master` != 0
				AND 	hb.active = 1
		');

		foreach ($foodsaver as $fs) {
			if ((int)$fs['master'] > 0) {
				$this->db->insertIgnore('fs_foodsaver_has_bezirk', [
					'foodsaver_id' => $fs['foodsaver_id'],
					'bezirk_id' => $fs['master'],
					'active' => 1,
					'added' => $this->db->now()
				]);
			}
		}
	}

	/**
	 * Lists all users that have a profile photo.
	 *
	 * @return array foodsaver Id and photo file name for each user
	 */
	public function listUsersWithPhoto(): array
	{
		return $this->db->fetch('SELECT id, photo FROM fs_foodsaver WHERE photo != ""');
	}

	/**
	 * Removes the profile photo file name for all users in the list.
	 *
	 * @param array $foodsaverIds a list of foodsaver IDs
	 */
	public function unsetUserPhotos(array $foodsaverIds): void
	{
		$this->db->update('fs_foodsaver', ['photo' => ''], ['id' => $foodsaverIds]);
	}
}
