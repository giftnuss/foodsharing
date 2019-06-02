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
			SELECT
				bezirk_id
			FROM
				fs_foodsaver_has_bezirk	
			WHERE
				active != :active	
			AND
				foodsaver_id = :foodsaver_id
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
	 */
	public function updateTeam(int $groupId, array $memberIds, array $leaderIds)
	{
		if ($memberIds) {
			// delete all members they're not in the submitted array
			$this->db->execute('
				DELETE
				FROM
					fs_foodsaver_has_bezirk
				WHERE
					bezirk_id = ' . (int)$groupId . '
				AND
					foodsaver_id NOT IN(' . implode(',', array_map('intval', $memberIds)) . ')
				AND
					active = 1
			');

			// insert new members
			$values = [
				'bezirk_id' => (int)$groupId,
				'active' => 1,
				'added' => $this->db->now()
			];
			foreach ($memberIds as $m) {
				$values['foodsaver_id'] = (int)$m;
				$this->db->insertIgnore('fs_foodsaver_has_bezirk', $values);
			}
		} else {
			$this->emptyMember($groupId);
		}

		// the same for the group admins
		if ($leaderIds) {
			// delete all group-admins (botschafter) they're not in the submitted array
			$this->db->execute('
				DELETE
				FROM
					fs_botschafter
				WHERE
					bezirk_id = ' . (int)$groupId . '
				AND
					foodsaver_id NOT IN(' . implode(',', array_map('intval', $leaderIds)) . ')
			');

			// insert new group-admins
			$values = ['bezirk_id' => (int)$groupId];
			foreach ($leaderIds as $m) {
				$values['foodsaver_id'] = (int)$m;
				$this->db->insertIgnore('fs_botschafter', $values);
			}
		} else {
			$this->emptyLeader($groupId);
		}
	}

	/**
	 * Delete all Leaders from a group.
	 *
	 * @param int $groupId
	 */
	private function emptyLeader(int $groupId): int
	{
		return $this->db->delete('fs_botschafter', ['bezirk_id' => $groupId]);
	}

	/**
	 * Delete all Leaders from a group.
	 *
	 * @param int $groupId
	 */
	private function emptyMember(int $groupId): int
	{
		return $this->db->delete(
			'fs_foodsaver_has_bezirk',
			[
				'bezirk_id' => $groupId,
				'active' => 1
			]
		);
	}

	public function getGroup(int $id): array
	{
		$group = $this->db->fetch('
			SELECT
				b.`id`,
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
				CONCAT(m.name,"@' . PLATFORM_MAILBOX_HOST . '") AS email
			FROM
				`fs_bezirk` b
			LEFT JOIN
				`fs_mailbox` m
			ON
				b.`mailbox_id` = m.`id`
			WHERE
				b.`id` = :bezirk_id
		', [':bezirk_id' => $id]);
		if ($group) {
			$group['member'] = $this->db->fetchAll('
				SELECT 
					`id`, 
					`name`, 
					`photo`
				FROM
					`fs_foodsaver` fs
				INNER JOIN
					`fs_foodsaver_has_bezirk` hb
				ON
					hb.foodsaver_id = fs.id
				WHERE  
					hb.`bezirk_id` = :bezirk_id
				AND
					hb.`active` = 1
			', [':bezirk_id' => $id]);
			$group['leader'] = $this->db->fetchAll('
				SELECT
					`id`,
					`name`,
					`photo`
				FROM
					`fs_foodsaver` fs
				INNER JOIN
					`fs_botschafter` hb
				ON
					hb.`foodsaver_id` = fs.`id`
				WHERE
					hb.`bezirk_id` = :bezirk_id
			', [':bezirk_id' => $id]);
		} else {
			return ['X'];
		}

		return $group;
	}

	public function addToGroup(int $groupId, int $fsId): int
	{
		return $this->db->insertOrUpdate(
			'fs_foodsaver_has_bezirk',
			[
				'foodsaver_id' => $fsId,
				'bezirk_id' => $groupId,
				'active' => 1,
				'added' => $this->db->now()
			]
		);
	}

	public function listMemberGroups(int $fsId): array
	{
		return $this->db->fetchAll('
			SELECT
				b.`id`,
				b.`name`,
				b.`teaser`,
				b.`photo`
			FROM
				fs_bezirk b
			INNER JOIN
				fs_foodsaver_has_bezirk hb
			ON
				hb.bezirk_id = b.id
			WHERE
				hb.`foodsaver_id` = :foodsaver_id
			AND
				b.`type` = :bezirk_type
			ORDER BY
				b.`name`
		', [':foodsaver_id' => $fsId, ':bezirk_type' => Type::WORKING_GROUP]);
	}

	public function listGroups(int $parentId): array
	{
		$groups = $this->db->fetchAll('
			SELECT
				b.`id`,
				b.`name`,
				b.`parent_id`,
				b.`teaser`,
				b.`photo`,
				b.`apply_type`,
				b.`banana_count`,
				b.`week_num`,
				b.`fetch_count`,
				CONCAT(m.name,"@' . PLATFORM_MAILBOX_HOST . '") AS email
			FROM
				fs_bezirk b
			LEFT JOIN
				fs_mailbox m
			ON
				b.`mailbox_id` = m.`id`
			WHERE
				b.`parent_id` = :parent_id
			AND
				b.`type` = :bezirk_type
			ORDER BY
				`name`
		', [':parent_id' => $parentId, ':bezirk_type' => Type::WORKING_GROUP]);
		if ($groups) {
			foreach ($groups as $i => $g) {
				$members = $this->db->fetchAll('
					SELECT
						`id`, 
						`name`, 
						`photo`
					FROM
						`fs_foodsaver` fs
					INNER JOIN
						`fs_foodsaver_has_bezirk` hb
					ON
						hb.`foodsaver_id` = fs.id
					WHERE
						hb.`bezirk_id` = :bezirk_id
					AND
						hb.`active` = 1
				', [':bezirk_id' => $g['id']]);
				$leaders = $this->db->fetchAll('
					SELECT
						`id`,
						`name`,
						`photo`
					FROM
						`fs_foodsaver` fs
					INNER JOIN
						`fs_botschafter` hb
					ON
						hb.`foodsaver_id` = fs.id
					WHERE
						hb.`bezirk_id` = :bezirk_id
				', [':bezirk_id' => $g['id']]);
				$groups[$i]['members'] = $members ? $members : [];
				$groups[$i]['leaders'] = $leaders ? $leaders : [];
			}

			return $groups;
		}

		return [];
	}

	public function groupApply(int $groupId, int $fsId, string $application): int
	{
		return $this->db->insertOrUpdate(
			'fs_foodsaver_has_bezirk',
			[
				'foodsaver_id' => $fsId,
				'bezirk_id' => $groupId,
				'active' => 0,
				'added' => $this->db->now(),
				'application' => strip_tags($application)
			]
		);
	}
	
	public function getFsWithMail(int $fsId): array
	{
		return $this->db->fetch('
			SELECT
				fs.`id`,
				fs.`name`,
				IF(mb.`name` IS NULL, fs.`email`, CONCAT(mb.`name`,"@' . PLATFORM_MAILBOX_HOST . '")) AS email
			FROM
				fs_foodsaver `fs`
			LEFT JOIN
				fs_mailbox `mb`
			ON
				fs.`mailbox_id` = mb.`id`
			WHERE
				fs.`id` = :fs_id
		', [':fs_id' => $fsId]);
	}

	public function getGroupMail(int $id)
	{
		return $this->db->fetchValue('
			SELECT 
				CONCAT(mb.name,"@' . PLATFORM_MAILBOX_HOST . '")
			FROM 	
				fs_bezirk bz
			INNER JOIN
				fs_mailbox mb
			ON
				bz.`mailbox_id` = mb.`id`				
			WHERE 
				bz.id = :bezirk_id
		', [':bezirk_id' => $id]);
	}

	public function updateGroup(int $id, array $data): int
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
			['id' => $id]
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
			// 604800 = sekunden pro woche
			$weeks = (int)round((time() - $time) / 604800);

			return [
				'weeks' => $weeks,
				'fetchcount' => $ret['stat_fetchcount'],
				'bananacount' => $ret['stat_bananacount'],
			];
		}
	}

	public function getCountryGroups(): array
	{
		return $this->db->fetchAll('
			SELECT 	
				`id`,
				`name`,
				`parent_id`
			FROM 	
				fs_bezirk
			WHERE
				`type` = :type
		', [':type' => Type::COUNTRY]);
	}
}
