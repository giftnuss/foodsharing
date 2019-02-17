<?php

namespace Foodsharing\Modules\Foodsaver;

use Foodsharing\Modules\Core\BaseGateway;

class FoodsaverGateway extends BaseGateway
{
	public function getFoodsaver($bezirk_id)
	{
		$and = ' AND 		fb.`bezirk_id` = ' . (int)$bezirk_id . '';
		if (is_array($bezirk_id)) {
			if (is_array(end($bezirk_id))) {
				$tmp = $bezirk_id;
				$bezirk_id = array();
				foreach ($tmp as $b) {
					$bezirk_id[$b['id']] = $b['id'];
				}
			}

			$and = ' AND 		fb.`bezirk_id` IN(' . implode(',', $bezirk_id) . ')';
		}

		return $this->db->fetchAll('
			SELECT 		fs.id,
						CONCAT(fs.`name`, " ", fs.`nachname`) AS `name`,
						fs.`name` AS vorname,
						fs.`anschrift`,
						fs.`email`,
						fs.`telefon`,
						fs.`handy`,
						fs.`plz`,
						fs.`geschlecht`

			FROM 		fs_foodsaver_has_bezirk fb,
						`fs_foodsaver` fs

			WHERE 		fb.foodsaver_id = fs.id
			AND			fs.deleted_at IS NULL ' . $and
		);
	}

	public function getFoodsaverDetails($fs_id)
	{
		return $this->db->fetchByCriteria(
			'fs_foodsaver',
			[
				'id',
				'admin',
				'orgateam',
				'bezirk_id',
				'photo',
				'rolle',
				'type',
				'verified',
				'name',
				'nachname',
				'lat',
				'lon',
				'email',
				'token',
				'mailbox_id',
				'option',
				'geschlecht',
				'privacy_policy_accepted_date',
				'privacy_notice_accepted_date'
			],
			['id' => $fs_id]
		);
	}

	public function getFoodsaverBasics($fsid)
	{
		if ($fs = $this->db->fetch('
			SELECT 	fs.`name`,
					fs.nachname,
					fs.bezirk_id,
					fs.rolle,
					fs.photo,
					fs.geschlecht,
					fs.stat_fetchweight,
					fs.sleep_status,
					fs.id

			FROM 	`fs_foodsaver` fs

			WHERE fs.id = ' . (int)$fsid . '
		')
		) {
			$fs['bezirk_name'] = '';
			if ($fs['bezirk_id'] > 0) {
				$fs['bezirk_name'] = $this->db->fetchValueByCriteria('fs_bezirk', 'name', ['id' => $fs['bezirk_id']]);
			}

			return $fs;
		}

		return false;
	}

	public function getOne_foodsaver($id)
	{
		$out = $this->db->fetch('
			SELECT
				`id`,
				`bezirk_id`,
				`plz`,
				`stadt`,
				`lat`,
				`lon`,
				`email`,
				`name`,
				`nachname`,
				`anschrift`,
				`telefon`,
				`handy`,
				`geschlecht`,
				`geb_datum`,
				`anmeldedatum`,
				`photo`,
				`photo_public`,
				`about_me_public`,
				`orgateam`,
				`data`,
				`rolle`,
				`position`,
				`tox`,
				`github`,
				`twitter`,
				`homepage`
			FROM 		`fs_foodsaver`
			WHERE 		`id` = :id',
			[':id' => $id]
		);

		$bot = $this->db->fetchAll('
			SELECT `fs_bezirk`.`name`,
				   `fs_bezirk`.`id` 
			FROM `fs_bezirk`,
				 fs_botschafter
			WHERE `fs_botschafter`.`bezirk_id` = `fs_bezirk`.`id` 
			AND `fs_botschafter`.foodsaver_id = :id',
			[':id' => $id]
		);

		if ($bot) {
			$out['botschafter'] = $bot;
		}

		return $out;
	}

	public function getBotschafter($bezirk_id): array
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`email`,
					fs.`name`,
					fs.`name` AS `vorname`,
					fs.`nachname`,
					fs.`photo`,
					fs.`geschlecht`

			FROM `fs_foodsaver` fs,
			`fs_botschafter`

			WHERE fs.id = `fs_botschafter`.`foodsaver_id`

			AND `fs_botschafter`.`bezirk_id` = :id
			AND		fs.deleted_at IS NULL',
			[':id' => $bezirk_id]
		);
	}

	public function getBezirkCountForBotschafter($fs_id): int
	{
		return $this->db->count('fs_botschafter', ['foodsaver_id' => $fs_id]);
	}

	public function getAllBotschafter()
	{
		return $this->db->fetchAll('
			SELECT 		fs.`id`,
						fs.`name`,
						fs.`nachname`,
						fs.`geschlecht`,
						fs.`email`

			FROM 		`fs_foodsaver` fs
			WHERE		fs.id
			IN			(SELECT foodsaver_id
						FROM `fs_fs_botschafter` b
						LEFT JOIN `fs_bezirk` bz
						ON b.bezirk_id = bz.id
						WHERE bz.type != 7
						)
			AND		fs.deleted_at IS NULL'
		);
	}

	public function getAllFoodsaver()
	{
		return $this->db->fetchAll('
			SELECT 		fs.id,
						CONCAT(fs.`name`, " ", fs.`nachname`) AS `name`,
						fs.`anschrift`,
						fs.`email`,
						fs.`telefon`,
						fs.`handy`,
						fs.plz

			FROM 		`fs_foodsaver` fs
			WHERE		fs.deleted_at IS NULL AND fs.`active` = 1
		');
	}

	public function getAllFoodsaverNoBotschafter()
	{
		$foodsaver = $this->getAllFoodsaver();
		$out = array();

		$botschafter = $this->getAllBotschafter();
		$bot = array();
		foreach ($botschafter as $b) {
			$bot[$b['id']] = true;
		}

		foreach ($foodsaver as $fs) {
			if (!isset($bot[$fs['id']])) {
				$out[] = $fs;
			}
		}

		return $out;
	}

	public function getOrgateam()
	{
		return $this->db->fetchAll('
			SELECT 		`id`,
						`name`,
						`nachname`,
						`geschlecht`,
						`email`

			FROM 		`fs_foodsaver`

			WHERE 		`orgateam` = 1
		');
	}

	public function getFsMap($bezirk_id)
	{
		return $this->db->fetchAll(
			'SELECT `id`,`lat`,`lon`,CONCAT(`name`," ",`nachname`)
			AS `name`,`plz`,`stadt`,`anschrift`,`photo`
			FROM `fs_foodsaver`
			WHERE `active` = 1 
			AND `bezirk_id` = :id 
			AND `lat` != "" ',
			[':id' => $bezirk_id]
		);
	}

	public function xhrGetTagFsAll($bezirk_ids): array
	{
		return $this->db->fetchAll('
			SELECT	DISTINCT fs.`id`,
					CONCAT(fs.`name`," ",fs.`nachname` ) AS value

			FROM 	fs_foodsaver fs,
					fs_foodsaver_has_bezirk hb
			WHERE 	hb.foodsaver_id = fs.id
			AND 	hb.bezirk_id IN(' . implode(',', $bezirk_ids) . ')
			AND		fs.deleted_at IS NULL
		');
	}

	public function xhrGetFoodsaver($data): array
	{
		if (isset($data['bid'])) {
			throw new Exception('filterung by bezirkIds is not supported anymore');
		}

		$term = $data['term'];
		$term = trim($term);
		$term = preg_replace('/[^a-zA-ZäöüÖÜß]/', '', $term);
		$term = $term . '%';

		if (strlen($term) > 2) {
			$out = $this->db->fetchAll('
				SELECT		`id`,
							CONCAT_WS(" ", `name`, `nachname`, CONCAT("(", `id`, ")")) AS value
				FROM 		fs_foodsaver
				WHERE 		((`name` LIKE :term
				OR 			`nachname` LIKE :term2))
				AND			deleted_at IS NULL
			', [':term' => $term, ':term2' => $term]);

			return $out;
		}

		return array();
	}

	public function getEmailAdressen($region_ids)
	{
		$placeholders = $this->db->generatePlaceholders(count($region_ids));

		return $this->db->fetchAll('
				SELECT 	`id`,
						`name`,
						`nachname`,
						`email`,
						`geschlecht`

				FROM 	`fs_foodsaver`

				WHERE 	`bezirk_id` IN(' . $placeholders . ')
				AND		deleted_at IS NULL',
				$region_ids
			);
	}

	public function getAllEmailFoodsaver($newsletter = false, $only_foodsaver = true)
	{
		if ($only_foodsaver) {
			$min_rolle = 1;
		} else {
			$min_rolle = 0;
		}
		$where = "WHERE rolle >= $min_rolle";
		if ($newsletter !== false) {
			$where = "WHERE newsletter = 1 AND rolle >= $min_rolle";
		}

		return $this->db->fetchAll('
				SELECT 	`id`,`email`
				FROM `fs_foodsaver`
				' . $where . ' AND active = 1
				AND	deleted_at IS NULL
		');
	}

	public function getEmailBotFromBezirkList($bezirklist)
	{
		$list = array();
		foreach ($bezirklist as $i => $b) {
			if ($b > 0) {
				$list[$b] = $b;
			}
		}
		ksort($list);

		$query = array();
		foreach ($list as $b) {
			$query[] = (int)$b;
		}

		$foodsaver = $this->db->fetchAll('
			SELECT 			fs.`id`,
							fs.`name`,
							fs.`nachname`,
							fs.`geschlecht`,
							fs.`email`

			FROM 	`fs_foodsaver` fs,
					`fs_botschafter` b

			WHERE 	b.foodsaver_id = fs.id
			AND		b.`bezirk_id`  IN(' . implode(',', $query) . ')
			AND		fs.deleted_at IS NULL;
		');

		$out = array();
		foreach ($foodsaver as $fs) {
			$out[$fs['id']] = $fs;
		}

		return $out;
	}

	public function getEmailFoodSaverFromBezirkList($bezirklist)
	{
		$list = array();
		foreach ($bezirklist as $i => $b) {
			if ($b > 0) {
				$list[$b] = $b;
			}
		}
		ksort($list);

		$query = array();
		foreach ($list as $b) {
			$query[] = (int)$b;
		}

		$foodsaver = $this->db->fetchAll('
			SELECT 			fs.`id`,
							fs.`name`,
							fs.`nachname`,
							fs.`geschlecht`,
							fs.`email`

			FROM 	`fs_foodsaver` fs,
					`fs_foodsaver_has_bezirk` b

			WHERE 	b.foodsaver_id = fs.id
			AND		b.`bezirk_id` IN(' . implode(',', $query) . ')
			AND		fs.deleted_at IS NULL;
		');

		$out = array();
		foreach ($foodsaver as $fs) {
			$out[$fs['id']] = $fs;
		}

		return $out;
	}

	public function updateGroupMembers($bezirk, $foodsaver_ids, $leave_admins)
	{
		$rows_ins = 0;
		$rows_del = 0;
		if ($leave_admins) {
			$admins = $this->db->fetchAllValues('SELECT foodsaver_id FROM `fs_botschafter` b WHERE b.bezirk_id = ' . (int)$bezirk);
			if ($admins) {
				$foodsaver_ids = array_merge($foodsaver_ids, $admins);
			}
		}
		$ids = implode(',', array_map('intval', $foodsaver_ids));
		if ($ids) {
			$rows_del = $this->db->execute('DELETE FROM `fs_foodsaver_has_bezirk` WHERE bezirk_id = ' . (int)$bezirk . ' AND foodsaver_id NOT IN (' . $ids . ')')->rowCount();
			$insert_strings = array_map(function ($id) use ($bezirk) {
				return '(' . $id . ',' . $bezirk . ',1,NOW())';
			}, $foodsaver_ids);
			$insert_values = implode(',', $insert_strings);
			$rows_ins = $this->db->execute('INSERT IGNORE INTO `fs_foodsaver_has_bezirk` (foodsaver_id, bezirk_id, active, added) VALUES ' . $insert_values)->rowCount();
		} else {
			$rows_del = $this->db->execute('DELETE FROM `fs_foodsaver_has_bezirk` WHERE bezirk_id = ' . (int)$bezirk)->rowCount();
		}

		return array($rows_ins, $rows_del);
	}

	public function listActiveByRegion($id)
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`photo`,
					fs.`name`,
					fs.`nachname`,
					fs.sleep_status

			FROM 	`fs_foodsaver` fs,
					`fs_foodsaver_has_bezirk` c

			WHERE 	c.`foodsaver_id` = fs.id
			AND     fs.deleted_at IS NULL
			AND 	c.bezirk_id = :id
			AND 	c.active = 1
			AND 	fs.sleep_status = 0

			ORDER BY fs.`name`
		', ['id' => $id]);
	}

	public function listActiveWithFullNameByRegion($id)
	{
		return $this->db->fetchAll('

			SELECT 	fs.id,
					CONCAT(fs.`name`, " ", fs.`nachname`) AS `name`,
					fs.`name` AS vorname,
					fs.`anschrift`,
					fs.`email`,
					fs.`telefon`,
					fs.`handy`,
					fs.`plz`,
					fs.`geschlecht`

			FROM 	fs_foodsaver_has_bezirk fb,
					`fs_foodsaver` fs

			WHERE 	fb.foodsaver_id = fs.id
			AND 	fb.bezirk_id = :id
			AND 	fb.`active` = 1
			AND		fs.deleted_at IS NULL
		', ['id' => $id]);
	}

	public function listInactiveByRegion($id)
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`photo`,
					fs.`name`,
					fs.`nachname`,
					fs.sleep_status

			FROM 	`fs_foodsaver` fs,
					`fs_foodsaver_has_bezirk` c

			WHERE 	c.`foodsaver_id` = fs.id
			AND     fs.deleted_at IS NULL
			AND 	c.bezirk_id = :id
			AND 	c.active = 1
			AND 	fs.sleep_status > 0

			ORDER BY fs.`name`
		', ['id' => $id]);
	}

	public function listAmbassadorsByRegion($id)
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`photo`,
					fs.`name`,
					fs.`nachname`,
					fs.sleep_status

			FROM 	`fs_foodsaver` fs,
					`fs_botschafter` c

			WHERE 	c.`foodsaver_id` = fs.id
			AND     fs.deleted_at IS NULL
			AND 	c.bezirk_id = :id
		', ['id' => $id]);
	}

	/* retrieves the list of all bots for given bezirk or sub bezirk */
	public function getBotIds($bezirk, $include_bezirk_bot = true, $include_group_bot = false)
	{
		$where_type = '';
		if (!$include_bezirk_bot) {
			$where_type = 'bz.type = 7';
		} elseif (!$include_group_bot) {
			$where_type = 'bz.type <> 7';
		}

		return $this->db->fetchAllValues('SELECT DISTINCT bot.foodsaver_id FROM `fs_bezirk_closure` c
			LEFT JOIN `fs_bezirk` bz ON bz.id = c.bezirk_id
			INNER JOIN `fs_botschafter` bot ON bot.bezirk_id = c.bezirk_id
			INNER JOIN `fs_foodsaver` fs ON fs.id = bot.foodsaver_id
			WHERE c.ancestor_id = ' . (int)$bezirk . ' AND fs.deleted_at IS NULL AND ' . $where_type);
	}

	public function del_foodsaver($id)
	{
		$this->db->execute('
			INSERT INTO fs_foodsaver_archive
			(
				SELECT * FROM fs_foodsaver WHERE id = ' . (int)$id . '
			)
		');

		$this->db->execute('
            DELETE FROM fs_apitoken
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_application_has_wallpost
            WHERE application_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_basket_anfrage
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_botschafter
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_buddy
            WHERE foodsaver_id = ' . (int)$id . ' OR buddy_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_email_status
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_fairteiler_follower
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_foodsaver_has_bell
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_foodsaver_has_bezirk
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_foodsaver_has_contact
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_foodsaver_has_event
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_foodsaver_has_wallpost
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_mailbox_member
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_mailchange
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_pass_gen
            WHERE foodsaver_id = ' . (int)$id . ' OR bot_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_pass_request
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_quiz_session
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_rating
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->db->execute('
            DELETE FROM fs_theme_follower
            WHERE foodsaver_id = ' . (int)$id . '
		');

		// remove bananas given by this user
		$this->db->execute('
            DELETE FROM fs_rating
            WHERE rater_id = ' . (int)$id . '
		');

		$this->db->execute('UPDATE fs_foodsaver SET verified = 0,
			rolle = 0,
			plz = NULL,
			stadt = NULL,
			lat = NULL,
			lon = NULL,
			photo = NULL,
			email = NULL,
			passwd = NULL,
			name = NULL,
			nachname = NULL,
			anschrift = NULL,
			telefon = NULL,
			tox = NULL,
			github = NULL,
			twitter = NULL,
			handy = NULL,
			geb_datum = NULL,
			deleted_at = NOW()
			WHERE id = ' . (int)$id);
	}

	public function getFsAutocomplete($bezirk_id)
	{
		$and = 'AND fb.`bezirk_id` = ' . (int)$bezirk_id . '';
		if (is_array($bezirk_id)) {
			if (is_array(end($bezirk_id))) {
				$tmp = $bezirk_id;
				$bezirk_id = array();
				foreach ($tmp as $b) {
					$bezirk_id[$b['id']] = $b['id'];
				}
			}

			$and = 'AND fb.`bezirk_id` IN(' . implode(',', $bezirk_id) . ')';
		}

		return $this->db->fetchAll('
			SELECT 		fs.id,
						CONCAT(fs.`name`, " ", fs.`nachname`) AS `value`

			FROM 		fs_foodsaver_has_bezirk fb,
						`fs_foodsaver` fs

			WHERE 		fb.foodsaver_id = fs.id
			AND			fs.deleted_at IS NULL ' . $and
		);
	}

	public function updateProfile($fs_id, $data)
	{
		if (!isset($data['photo_public'])) {
			$data['photo_public'] = 0;
		}

		$fields = [
			'bezirk_id',
			'plz',
			'lat',
			'lon',
			'stadt',
			'anschrift',
			'telefon',
			'handy',
			'geb_datum',
			'about_me_public',
			'photo_public',
			'homepage',
			'twitter',
			'github',
			'position',
			'tox'
		];

		$fieldsToStripTags = [
			'plz',
			'lat',
			'lon',
			'stadt',
			'anschrift',
			'telefon',
			'handy',
			'about_me_public',
			'homepage',
			'twitter',
			'github',
			'position',
			'tox'
		];

		$clean_data = [];
		foreach ($fields as $field) {
			if (!array_key_exists($field, $data)) {
				continue;
			}
			$clean_data[$field] = in_array($field, $fieldsToStripTags, true) ? strip_tags($data[$field]) : $data[$field];
		}

		$this->db->update(
			'fs_foodsaver',
			$clean_data,
			['id' => $fs_id]
		);

		return true;
	}

	public function updatePhoto($fs_id, $photo)
	{
		$this->db->update(
			'fs_foodsaver',
			['photo' => strip_tags($photo)],
			['id' => $fs_id]
	);
	}

	public function getPhoto($fs_id)
	{
		return $this->db->fetchValueByCriteria('fs_foodsaver', 'photo', ['id' => $fs_id]);
	}

	public function emailExists($email)
	{
		return $this->db->exists('fs_foodsaver', ['email' => $email]);
	}

	/**
	 * set option is an key value store each var is avalable in the user session.
	 *
	 * @param string $key
	 * @param $val
	 */
	public function setOption($fs_id, $key, $val)
	{
		$options = array();
		if ($opt = $this->db->fetchValueByCriteria('fs_foodsaver', 'option', ['id' => $fs_id])) {
			$options = unserialize($opt);
		}

		$options[$key] = $val;

		return $this->db->update('fs_foodsaver', ['option' => serialize($options)], ['id' => $fs_id]);
	}
}
