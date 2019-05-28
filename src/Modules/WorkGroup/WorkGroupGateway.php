<?php

namespace Foodsharing\Modules\WorkGroup;

use DateTime;
use Foodsharing\Modules\Core\BaseGateway;

class WorkGroupGateway extends BaseGateway
{
	/*
	 * Own existing applications.
	 */
	public function getApplications($fsId)
	{
		$ret = $this->db->fetchAllValues(
			'SELECT `bezirk_id`
			FROM 	`fs_foodsaver_has_bezirk`	
			WHERE 	`active` != :active	
			AND 	foodsaver_id = :foodsaver_id',
			[':active' => 1, ':foodsaver_id' => (int)$fsId]
		);
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
	public function updateTeam($groupId, $memberIds, $leaderIds)
	{
		if ($memberIds) {
			// delete all members they're not in the submitted array
			$this->db->delete(
				'fs_foodsaver_has_bezirk',
				[
					'bezirk_id' => (int)$groupId,
					'foodsaver_id NOT' => array_map('intval', $memberIds),
					'active' => 1
				]
			);

			// insert new members
			$values = [
				'bezirk_id' => (int)$groupId,
				'active' => 1,
				'added' => (new DateTime('NOW -6 MONTH'))->format('Y-m-d H:i:s')
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
			$this->db->delete(
				'fs_botschafter',
				[
					'bezirk_id' => (int)$groupId,
					'foodsaver_id NOT' => array_map('intval', $leaderIds)
				]
			);

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
	private function emptyLeader($groupId)
	{
		return $this->db->delete('fs_botschafter', ['bezirk_id' => (int)$groupId]);
	}

	/**
	 * Delete all Leaders from a group.
	 *
	 * @param int $groupId
	 */
	private function emptyMember($groupId)
	{
		return $this->db->delete(
			'fs_foodsaver_has_bezirk',
			[
				'bezirk_id' => (int)$groupId,
				'active' => 1
			]
		);
	}

	public function getGroup($id)
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
				fs_bezirk b
			LEFT JOIN
				fs_mailbox m
			ON
				b.mailbox_id = m.id
			WHERE
				b.`id` = ' . (int)$id . '
		');
		if ($group) {
			$group['member'] = $this->db->fetchAll('
				SELECT 
					`id`, 
					`name`, 
					`photo`

				FROM
					`fs_foodsaver` fs,
					`fs_foodsaver_has_bezirk` hb

				WHERE 
					hb.foodsaver_id = fs.id

				AND 
					hb.bezirk_id = ' . (int)$id . '
				AND
					hb.`active` = 1
			');
			$group['leader'] = $this->db->fetchAll('
				SELECT
				`id`,
				`name`,
				`photo`

				FROM
				`fs_foodsaver` fs,
				`fs_botschafter` hb

				WHERE
				hb.foodsaver_id = fs.id

				AND
				hb.bezirk_id = ' . (int)$id . '
			');
		}

		return $group;
	}

	public function addToGroup($group_id, $fsId)
	{
		return $this->db->insertOrUpdate(
			'fs_foodsaver_has_bezirk',
			[
				'foodsaver_id' => (int)$fsId,
				'bezirk_id' => (int)$group_id,
				'active' => 1,
				'added' => (new DateTime('NOW -6 MONTH'))->format('Y-m-d H:i:s')
			]
		);
	}

	public function listMemberGroups($fsId)
	{
		return $this->db->fetchAll('
			SELECT
				b.`id`,
				b.`name`,
				b.`teaser`,
				b.`photo`
		
			FROM
				fs_bezirk b,
				fs_foodsaver_has_bezirk hb
		
			WHERE
				hb.bezirk_id = b.id
		
			AND
				hb.`foodsaver_id` = ' . (int)$fsId . '
		
			AND
				b.`type` = 7
		
			ORDER BY
				b.`name`
		');
	}

	public function listGroups($parent)
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
				b.mailbox_id = m.id
			WHERE
				b.`parent_id` = ' . (int)$parent . '
			AND
				b.`type` = 7
			ORDER BY
				`name`
		');
		if ($groups) {
			foreach ($groups as $i => $g) {
				$members = $this->db->fetchAll('
					SELECT 
						`id`, 
						`name`, 
						`photo`
						 
					FROM
						`fs_foodsaver` fs,
						`fs_foodsaver_has_bezirk` hb

					WHERE 
						hb.foodsaver_id = fs.id
						
					AND 
						hb.bezirk_id = ' . $g['id'] . '
					AND
						hb.`active` = 1
				');
				$leaders = $this->db->fetchAll('
						SELECT
						`id`,
						`name`,
						`photo`
							
						FROM
						`fs_foodsaver` fs,
						`fs_botschafter` hb
				
						WHERE
						hb.foodsaver_id = fs.id
				
						AND
						hb.bezirk_id = ' . $g['id'] . '
						');
				$groups[$i]['members'] = $members ? $members : [];
				$groups[$i]['leaders'] = $leaders ? $leaders : [];
			}

			return $groups;
		}

		return [];
	}

	public function groupApply($groupId, $fsId, $application)
	{
		return $this->db->insertOrUpdate(
			'fs_foodsaver_has_bezirk',
			[
				'foodsaver_id' => (int)$fsId,
				'bezirk_id' => (int)$groupId,
				'active' => 0,
				'added' => (new DateTime('NOW -6 MONTH'))->format('Y-m-d H:i:s'),
				'application' => strip_tags($application)
			]
		);
	}

	public function getFsMail($fsId)
	{
		return $this->db->fetchValue('
			SELECT
				CONCAT(mb.name,"@' . PLATFORM_MAILBOX_HOST . '")
		
			FROM
				fs_mailbox mb,
				fs_foodsaver fs
		
			WHERE
				fs.mailbox_id = mb.id
		
			AND
				fs.id = ' . (int)$fsId . '
		');
	}

	public function getGroupMail($id)
	{
		return $this->db->fetchValue('
			SELECT 
				CONCAT(mb.name,"@' . PLATFORM_MAILBOX_HOST . '")
				
			FROM 	
				fs_mailbox mb,
				fs_bezirk bz
				
			WHERE 
				bz.mailbox_id = mb.id
				
			AND
				bz.id = ' . (int)$id . '
		');
	}

	public function updateGroup($id, $data)
	{
		return $this->db->update(
			'fs_bezirk',
			[
				'name' => strip_tags($data['name']),
				'teaser' => strip_tags($data['teaser']),
				'photo' => strip_tags($data['photo']),
				'apply_type' => (int)$data['apply_type'],
				'banana_count' => (int)$data['banana_count'],
				'fetch_count' => (int)$data['fetch_count'],
				'week_num' => (int)$data['week_num']
			],
			['id' => (int)$id]
		);
	}

	public function getStats($fsId)
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
				'weeks' => (int)$weeks,
				'fetchcount' => (int)$ret['stat_fetchcount'],
				'bananacount' => (int)$ret['stat_bananacount'],
			];
		}
	}

	public function getCountryGroups()
	{
		return $this->db->fetchAll('
			SELECT 	
				`id`,
				`name`,
				`parent_id`
				
			FROM 	
				fs_bezirk
				
			WHERE
				`type` = 6
		');
	}
}
