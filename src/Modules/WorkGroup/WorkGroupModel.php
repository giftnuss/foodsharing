<?php

namespace Foodsharing\Modules\WorkGroup;

use Foodsharing\Lib\Db\Db;

class WorkGroupModel extends Db
{
	/*
	 * Eigene Vorhandene Bewerbungen
	 */
	public function getApplications($fsId)
	{
		if ($ret = $this->qCol('
			SELECT 	`bezirk_id`
			FROM 	`fs_foodsaver_has_bezirk`	
			WHERE 	`active` != 1	
			AND 	foodsaver_id = ' . (int)$fsId . '
		')
		) {
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
			$this->del('
				DELETE FROM 
					`fs_foodsaver_has_bezirk`
				
				WHERE 
					bezirk_id = ' . (int)$groupId . '
					
				AND
					foodsaver_id NOT IN(' . implode(',', $memberIds) . ')
				AND
					`active` = 1
			');

			$values = array();
			foreach ($memberIds as $m) {
				$values[] = '(' . (int)$m . ',' . $groupId . ',1,NOW())';
			}

			// insert new members
			$this->insert('
				INSERT IGNORE INTO `fs_foodsaver_has_bezirk`
				(
					`foodsaver_id`,
					`bezirk_id`,
					`active`,
					`added`
				)
				VALUES
				' . implode(',', $values) . '
				
			');
		} else {
			$this->emptyMember($groupId);
		}

		// the same for the group admins
		if ($leaderIds) {
			// delete all group-admins (botschafter) they're not in the submitted array
			$this->del('
				DELETE FROM
					`fs_botschafter`
			
				WHERE
					bezirk_id = ' . (int)$groupId . '
			
				AND
					foodsaver_id NOT IN(' . implode(',', $leaderIds) . ')
			');

			$values = array();
			foreach ($leaderIds as $m) {
				$values[] = '(' . (int)$m . ',' . $groupId . ')';
			}

			// insert new group-admins
			$this->insert('
				INSERT IGNORE INTO `fs_botschafter`
				(
					`foodsaver_id`,
					`bezirk_id`
				)
				VALUES
				' . implode(',', $values) . '
			
			');
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
		return $this->del('
			DELETE FROM `fs_botschafter`
			WHERE bezirk_id = ' . (int)$groupId . '
		');
	}

	/**
	 * Delete all Leaders from a group.
	 *
	 * @param int $groupId
	 */
	private function emptyMember($groupId)
	{
		return $this->del('
			DELETE FROM `fs_foodsaver_has_bezirk`
			WHERE bezirk_id = ' . (int)$groupId . '
			AND
			`active` = 1
		');
	}

	public function getGroup($id)
	{
		if ($group = $this->qRow('
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
				CONCAT(m.name,"@' . DEFAULT_EMAIL_HOST . '") AS email
			FROM
				fs_bezirk b
			LEFT JOIN
				fs_mailbox m
			ON
				b.mailbox_id = m.id
			WHERE
				b.`id` = ' . (int)$id . '
		')
		) {
			$group['member'] = $this->q('
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
			$group['leader'] = $this->q('
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
		return $this->insert('
			REPLACE INTO `fs_foodsaver_has_bezirk`(`foodsaver_id`, `bezirk_id`, `active`, `added`) 
			VALUES (
				' . (int)$fsId . ',
				' . (int)$group_id . ',
				1,
				NOW()
			)		
		');
	}

	public function listMemberGroups($fsId)
	{
		return $this->q('
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
		if ($groups = $this->q('
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
				CONCAT(m.name,"@' . DEFAULT_EMAIL_HOST . '") AS email
				
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
		')
		) {
			foreach ($groups as $i => $g) {
				$members = $this->q('
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
				$leaders = $this->q('
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
		return $this->insert('
			REPLACE INTO  `fs_foodsaver_has_bezirk`
				
			(`foodsaver_id`, `bezirk_id`, `active`, `added`,`application`) 
			VALUES 
			(' . (int)$fsId . ',' . (int)$groupId . ',0,NOW(),' . $this->strval($application) . ')		
		');
	}

	public function getFsMail($fsId)
	{
		return $this->qOne('
		
			SELECT
				CONCAT(mb.name,"@' . DEFAULT_EMAIL_HOST . '")
		
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
		return $this->qOne('

			SELECT 
				CONCAT(mb.name,"@' . DEFAULT_EMAIL_HOST . '")
				
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
		return $this->update('
				
			UPDATE 
				`fs_bezirk`
				
			SET 	
				`name` = ' . $this->strval($data['name']) . ',
				`teaser` = ' . $this->strval($data['teaser']) . ',
				`photo` = ' . $this->strval($data['photo']) . ',
				`apply_type` = ' . (int)$data['apply_type'] . ',
				`banana_count` = ' . (int)$data['banana_count'] . ',
				`fetch_count` = ' . (int)$data['fetch_count'] . ',
				`week_num` = ' . (int)$data['week_num'] . '
			WHERE
				`id` = ' . (int)$id . '
				
		');
	}

	public function getStats($fsId)
	{
		if ($ret = $this->getValues(array('anmeldedatum', 'stat_fetchcount', 'stat_bananacount'), 'foodsaver', $fsId)) {
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
		return $this->q('

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
