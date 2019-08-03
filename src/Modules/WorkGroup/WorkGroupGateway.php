<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\DBConstants\Region\Type;

class WorkGroupGateway extends BaseGateway
{
	/*
	 * Own existing applications.
	 */
	public function getApplications(int $fsId): array
	{
		$ret = $this->db->fetchAllValues('
			SELECT	`bezirk_id`
			FROM	`fs_foodsaver_has_bezirk`
			WHERE	`active` != :active
			AND		`foodsaver_id` = :foodsaver_id
		', [':active' => 1, ':foodsaver_id' => $fsId]);
		if ($ret) {
			$out = array();
			foreach ($ret as $gid) {
				$out[$gid] = $gid;
			}

			return $out;
		}

		return array();
	}

	/**
	 * Updates Group Members and Group-Admins.
	 *
	 * @param int $regionId
	 * @param array $memberIds
	 * @param array $leaderIds
	 */
	public function updateTeam(int $regionId, array $memberIds, array $leaderIds): void
	{
		$this->deleteSubscriptions($regionId, $memberIds, false);

		if ($memberIds) {
			// delete all members if they're not in the submitted array
			$this->db->execute('
				DELETE
				FROM	`fs_foodsaver_has_bezirk`
				WHERE	`bezirk_id` = ' . $regionId . '
				AND		`foodsaver_id` NOT IN(' . implode(',', array_map('intval', $memberIds)) . ')
				AND		`active` = 1
			');

			// insert new members
			foreach ($memberIds as $m) {
				$this->db->insertIgnore(
					'fs_foodsaver_has_bezirk',
					[
						'foodsaver_id' => (int)$m,
						'bezirk_id' => $regionId,
						'active' => 1,
						'added' => $this->db->now()
					]
				);
			}
		} else {
			$this->emptyMember($regionId);
		}

		// the same for the group admins
		$this->deleteSubscriptions($regionId, $leaderIds, true);

		if ($leaderIds) {
			// delete all group-admins (botschafter) if they're not in the submitted array
			$this->db->execute('
				DELETE
				FROM	`fs_botschafter`
				WHERE	`bezirk_id` = ' . $regionId . '
				AND		`foodsaver_id` NOT IN(' . implode(',', array_map('intval', $leaderIds)) . ')
			');

			// insert new group-admins
			foreach ($leaderIds as $m) {
				$this->db->insertIgnore(
					'fs_botschafter',
					[
						'foodsaver_id' => (int)$m,
						'bezirk_id' => $regionId
					]
				);
			}
		} else {
			$this->emptyLeader($regionId);
		}
	}

	/**
	 * Delete all Leaders from a group.
	 *
	 * @param int $regionId
	 *
	 * @return int
	 */
	private function emptyLeader(int $regionId): int
	{
		return $this->db->delete('fs_botschafter', ['bezirk_id' => $regionId]);
	}

	/**
	 * Delete all members from a group.
	 *
	 * @param int $regionId
	 *
	 * @return int
	 */
	private function emptyMember(int $regionId): int
	{
		return $this->db->delete(
			'fs_foodsaver_has_bezirk',
			[
				'bezirk_id' => $regionId,
				'active' => 1
			]
		);
	}

	/**
	 * Removes the forum subscriptions for all deleted members in the region.
	 *
	 * @param int $regionId id of the group
	 * @param array $memberIds list of remaining members, or null to remove all
	 * @param bool $leaders if the botschafter table should be used
	 */
	private function deleteSubscriptions(int $regionId, array $memberIds, bool $leaders)
	{
		$foodsaverTableName = $leaders ? 'fs_botschafter' : 'fs_foodsaver_has_bezirk';
		$themeIds = $this->db->fetchAllValuesByCriteria('fs_bezirk_has_theme', 'theme_id', ['bezirk_id' => $regionId]);

		if ($themeIds && !empty($themeIds)) {
			$query = '
				DELETE	tf.*
				FROM		`fs_theme_follower` tf
				JOIN		`fs_bezirk_has_theme` ht
				ON			ht.`theme_id` = tf.`theme_id`
				LEFT JOIN	`' . $foodsaverTableName . '` b
				ON			b.`bezirk_id` = ht.`bezirk_id`
				AND			b.`foodsaver_id` = tf.`foodsaver_id`
				WHERE		tf.`theme_id` IN (' . implode(',', array_map('intval', $themeIds)) . ')
			';
			if ($memberIds && !empty($memberIds)) {
				$query .= 'AND	tf.`foodsaver_id` NOT IN(' . implode(',', array_map('intval', $memberIds)) . ')';
			}

			$this->db->execute($query);
		}
	}

	public function getGroup(int $regionId): array
	{
		$group = $this->db->fetch('
			SELECT	b.`id`,
					b.`name`,
					b.`parent_id`,
					b.`teaser`,
					b.`photo`,
					b.`email_name`,
					b.`apply_type`,
					b.`banana_count`,
					b.`week_num`,
					b.`fetch_count`,
					b.`type`,
					CONCAT(m.`name`,"@' . PLATFORM_MAILBOX_HOST . '") AS email
			FROM		`fs_bezirk` b
			LEFT JOIN	`fs_mailbox` m
			ON			b.`mailbox_id` = m.`id`
			WHERE	b.`id` = :bezirk_id
		', [':bezirk_id' => $regionId]);
		if ($group) {
			$group['member'] = $this->db->fetchAll('
				SELECT		`id`,
							`name`,
							`photo`
				FROM		`fs_foodsaver` fs
				INNER JOIN	`fs_foodsaver_has_bezirk` hb
				ON			hb.`foodsaver_id` = fs.`id`
				WHERE		hb.`bezirk_id` = :bezirk_id
				AND			hb.`active` = 1
			', [':bezirk_id' => $regionId]);
			$group['leader'] = $this->db->fetchAll('
				SELECT		`id`,
							`name`,
							`photo`
				FROM		`fs_foodsaver` fs
				INNER JOIN	`fs_botschafter` hb
				ON			hb.`foodsaver_id` = fs.`id`
				WHERE		hb.`bezirk_id` = :bezirk_id
			', [':bezirk_id' => $regionId]);
		}

		return $group;
	}

	public function addToGroup(int $regionId, int $fsId): int
	{
		return $this->db->insertOrUpdate(
			'fs_foodsaver_has_bezirk',
			[
				'foodsaver_id' => $fsId,
				'bezirk_id' => $regionId,
				'active' => 1,
				'added' => $this->db->now()
			]
		);
	}

	public function listMemberGroups(int $fsId): array
	{
		return $this->db->fetchAll('
			SELECT		b.`id`,
						b.`name`,
						b.`teaser`,
						b.`photo`
			FROM		`fs_bezirk` b
			INNER JOIN	`fs_foodsaver_has_bezirk` hb
			ON			hb.`bezirk_id` = b.`id`
			WHERE		hb.`foodsaver_id` = :foodsaver_id
			AND			b.`type` = :bezirk_type
			ORDER BY	b.`name`
		', [':foodsaver_id' => $fsId, ':bezirk_type' => Type::WORKING_GROUP]);
	}

	public function listGroups(int $parentId): array
	{
		$groups = $this->db->fetchAll('
			SELECT		b.`id`,
						b.`name`,
						b.`parent_id`,
						b.`teaser`,
						b.`photo`,
						b.`apply_type`,
						b.`banana_count`,
						b.`week_num`,
						b.`fetch_count`,
						CONCAT(m.`name`,"@' . PLATFORM_MAILBOX_HOST . '") AS email
			FROM		`fs_bezirk` b
			LEFT JOIN	`fs_mailbox` m
			ON			b.`mailbox_id` = m.`id`
			WHERE		b.`parent_id` = :parent_id
			AND			b.`type` = :bezirk_type
			ORDER BY	`name`
		', [':parent_id' => $parentId, ':bezirk_type' => Type::WORKING_GROUP]);
		if ($groups) {
			foreach ($groups as $i => $g) {
				$members = $this->db->fetchAll('
					SELECT		`id`,
								`name`,
								`photo`
					FROM		`fs_foodsaver` fs
					INNER JOIN	`fs_foodsaver_has_bezirk` hb
					ON			hb.`foodsaver_id` = fs.id
					WHERE		hb.`bezirk_id` = :bezirk_id
					AND			hb.`active` = 1
				', [':bezirk_id' => $g['id']]);
				$leaders = $this->db->fetchAll('
					SELECT		`id`,
								`name`,
								`photo`
					FROM		`fs_foodsaver` fs
					INNER JOIN	`fs_botschafter` hb
					ON			hb.`foodsaver_id` = fs.id
					WHERE		hb.`bezirk_id` = :bezirk_id
				', [':bezirk_id' => $g['id']]);
				$groups[$i]['members'] = $members ? $members : [];
				$groups[$i]['leaders'] = $leaders ? $leaders : [];
			}

			return $groups;
		}

		return [];
	}

	public function groupApply(int $regionId, int $fsId, string $application): int
	{
		return $this->db->insertOrUpdate(
			'fs_foodsaver_has_bezirk',
			[
				'foodsaver_id' => $fsId,
				'bezirk_id' => $regionId,
				'active' => 0,
				'added' => $this->db->now(),
				'application' => strip_tags($application)
			]
		);
	}

	public function getFsWithMail(int $fsId): array
	{
		return $this->db->fetch('
			SELECT		fs.`id`,
						fs.`name`,
						IF(mb.`name` IS NULL, fs.`email`, CONCAT(mb.`name`,"@' . PLATFORM_MAILBOX_HOST . '")) AS email
			FROM		`fs_foodsaver` fs
			LEFT JOIN	`fs_mailbox` mb
			ON			fs.`mailbox_id` = mb.`id`
			WHERE		fs.`id` = :fs_id
		', [':fs_id' => $fsId]);
	}

	public function getGroupMail(int $regionId)
	{
		return $this->db->fetchValue('
			SELECT		CONCAT(mb.`name`,"@' . PLATFORM_MAILBOX_HOST . '")
			FROM		`fs_bezirk` bz
			INNER JOIN	`fs_mailbox` mb
			ON			bz.`mailbox_id` = mb.`id`
			WHERE		bz.`id` = :bezirk_id
		', [':bezirk_id' => $regionId]);
	}

	public function updateGroup(int $regionId, array $data): int
	{
		return $this->db->update(
			'fs_bezirk',
			[
				'name' => strip_tags($data['name']),
				'teaser' => strip_tags($data['teaser']),
				'photo' => strip_tags($data['photo']),
				'apply_type' => $data['apply_type'],
				'banana_count' => $data['banana_count'],
				'fetch_count' => $data['fetch_count'],
				'week_num' => $data['week_num']
			],
			['id' => $regionId]
		);
	}

	public function getStats(int $fsId): array
	{
		$ret = $this->db->fetchByCriteria(
			'fs_foodsaver',
			['anmeldedatum', 'stat_fetchcount', 'stat_bananacount'],
			['id' => $fsId]
		);
		if ($ret) {
			$time = strtotime($ret['anmeldedatum']);
			// 604800 = seconds per week
			$weeks = (int)round((time() - $time) / 604800);

			return [
				'weeks' => $weeks,
				'fetchcount' => $ret['stat_fetchcount'],
				'bananacount' => $ret['stat_bananacount'],
			];
		}

		return [];
	}

	public function getCountryGroups(): array
	{
		return $this->db->fetchAll('
			SELECT	`id`,
					`name`,
					`parent_id`
			FROM	`fs_bezirk`
			WHERE	`type` = :type
		', [':type' => Type::COUNTRY]);
	}
}
