<?php

namespace Foodsharing\Lib\Db;

use Foodsharing\Lib\Session\S;

class ManualDb extends Db
{
	public function add_message_tpl($data)
	{
		$id = $this->insert('
			INSERT INTO 	`fs_message_tpl`
			(
			`language_id`,
			`name`,
			`subject`,
			`body`
			)
			VALUES
			(
			' . (int)$data['language_id'] . ',
			' . $this->strval($data['name']) . ',
			' . $this->strval($data['subject']) . ',
			' . $this->strval($data['body'], true) . '
			)');

		return $id;
	}

	public function getFaqIntern()
	{
		return $this->q('SELECT `id`, `answer`, `name` FROM `fs_faq`');
	}

	public function getFsMap($bezirk_id)
	{
		$bezirk_id = (int)$bezirk_id;
		if ($bezirk_id > 0) {
			return $this->q('SELECT `id`,`lat`,`lon`,CONCAT(`name`," ",`nachname`) AS `name`,`plz`,`stadt`,`anschrift`,`photo` FROM `fs_foodsaver` WHERE `active` = 1 AND `bezirk_id` = ' . (int)$bezirk_id . ' AND `lat` != "" ');
		}
	}

	/**
	 * Searches the given term in the database of regions, foodsavers and companies.
	 *
	 * @param string $q Query string / search term
	 *
	 * @return array Array of regions, foodsavers and comanies containing the search term
	 */
	public function search($q)
	{
		$out = array();

		$children = false;
		//$bezirk_id = (int)$this->getCurrentBezirkId();
		if (!S::may('bot') && $bezirk_id > 0) {
			$children = S::getBezirkIds();
		}

		if (S::may('fs')) {
			if ($res = $this->searchTable('fs_bezirk', array('name'), $q, array(
				'name' => '`name`',
				'click' => 'CONCAT("goTo(\'/?page=bezirk&bid=",`id`,"\');")',
				'teaser' => 'CONCAT("")'
			))
			) {
				$out['bezirk'] = $res;
			}
		}

		if (S::may('fs')) {
			if ($res = $this->searchTable('fs_foodsaver', array('name', 'nachname', 'plz', 'stadt'), $q, array(
				'name' => 'CONCAT(`name`," ",`nachname`)',
				'click' => 'CONCAT("profile(",`id`,");")',
				'teaser' => 'stadt'
			), $children)
			) {
				$out['foodsaver'] = $res;
			}
		}
		if (S::may('fs')) {
			if ($res = $this->searchTable('fs_betrieb', array('name', 'stadt', 'plz'), $q, array(
				'name' => '`name`',
				'click' => 'CONCAT("betrieb(",`id`,");")',
				'teaser' => 'CONCAT(`str`,", ",`plz`," ",`stadt`)'
			), $children)
			) {
				$out['betrieb'] = $res;
			}
		}

		return $out;
	}

	public function searchTable($table, $fields, $query, $show = array(), $childs = false)
	{
		$q = trim($query);

		str_replace(array(',', ';', '+', '.'), ' ', $q);

		do {
			$q = str_replace('  ', ' ', $q);
		} while (strpos($q, '  ') !== false);

		$terms = explode(' ', $q);

		foreach ($terms as $i => $t) {
			$terms[$i] = $this->strval('%' . $t . '%');
		}

		$fsql = 'CONCAT(' . implode(',', $fields) . ')';

		$fs_sql = '';
		if ($childs !== false) {
			$fs_sql = ' AND bezirk_id IN(' . implode(',', $childs) . ')';
		}

		return $this->q('
			SELECT 	`id`,
					 ' . $show['name'] . ' AS name,
					 ' . $show['click'] . ' AS click,
					 ' . $show['teaser'] . ' AS teaser


			FROM 	' . $table . '

			WHERE ' . $fsql . ' LIKE ' . implode(' AND ' . $fsql . ' LIKE ', $terms) . '
			' . $fs_sql . '

			ORDER BY `name`
		');
	}

	public function passGen($fsid)
	{
		return $this->sql('INSERT INTO `fs_pass_gen`(`foodsaver_id`,`date`,`bot_id`)VALUES(' . (int)$fsid . ',NOW(),' . $this->func->fsId() . ')');
	}

	public function getFsAutocomplete($bezirk_id)
	{
		$and = 'AND 		fb.`bezirk_id` = ' . (int)$bezirk_id . '';
		if (is_array($bezirk_id)) {
			if (is_array(end($bezirk_id))) {
				$tmp = $bezirk_id;
				$bezirk_id = array();
				foreach ($tmp as $b) {
					$bezirk_id[$b['id']] = $b['id'];
				}
			}

			$and = 'AND 		fb.`bezirk_id` IN(' . implode(',', $bezirk_id) . ')';
		}

		return $this->q('
			SELECT 		fs.id,
						CONCAT(fs.`name`, " ", fs.`nachname`) AS `value`

			FROM 		fs_foodsaver_has_bezirk fb,
						`fs_foodsaver` fs

			WHERE 		fb.foodsaver_id = fs.id
			AND			fs.deleted_at IS NULL
			' . $and . '
		');
	}

	public function getFoodsaver($bezirk_id)
	{
		$and = 'AND 		fb.`bezirk_id` = ' . (int)$bezirk_id . '';
		if (is_array($bezirk_id)) {
			if (is_array(end($bezirk_id))) {
				$tmp = $bezirk_id;
				$bezirk_id = array();
				foreach ($tmp as $b) {
					$bezirk_id[$b['id']] = $b['id'];
				}
			}

			$and = 'AND 		fb.`bezirk_id` IN(' . implode(',', $bezirk_id) . ')';
		}

		return $this->q('
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
			AND			fs.deleted_at IS NULL
			' . $and . '
		');
	}

	public function getBezirkByParent($parent_id)
	{
		$sql = 'AND 		`type` != 7';
		if ($this->func->isOrgaTeam()) {
			$sql = '';
		}

		return $this->q('
			SELECT
				`id`,
				`name`,
				`has_children`,
				`parent_id`,
				`type`,
				`master`

			FROM 		`fs_bezirk`

			WHERE 		`parent_id` = ' . (int)$parent_id . '
			AND id != 0
			' . $sql . '

			ORDER BY 	`name`');
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

		return $this->q('
				SELECT 	`id`,`email`
				FROM `fs_foodsaver`
				' . $where . ' AND active = 1
				AND	deleted_at IS NULL
		');
	}

	public function xhrGetTagFs($bezirk_id)
	{
		$parent = (int)$this->getVal('parent_id', 'bezirk', $bezirk_id);

		return $this->q('
				SELECT	`id`,CONCAT(`name`," ",`nachname` ) AS value
				FROM 	fs_foodsaver
				WHERE 	`bezirk_id` IN(' . implode(',', $this->getChildBezirke($bezirk_id)) . ') AND deleted_at IS NULL');
	}

	public function xhrGetTagFsAll()
	{
		return $this->q('
				SELECT	DISTINCT fs.`id`,
						CONCAT(fs.`name`," ",fs.`nachname` ) AS value

				FROM 	fs_foodsaver fs,
						fs_foodsaver_has_bezirk hb
				WHERE 	hb.foodsaver_id = fs.id
				AND 	hb.bezirk_id IN(' . implode(',', S::getBezirkIds()) . ')
				AND		fs.deleted_at IS NULL
		');
	}

	public function xhrGetFoodsaver($data)
	{
		$term = $data['term'];
		$term = trim($term);
		$term = preg_replace('/[^a-zA-ZäöüÖÜß]/', '', $term);

		$bezirk = '';
		if (isset($data['bid'])) {
			if (is_array($data['bid'])) {
				$bezirk = 'AND bezirk_id IN(' . implode(',', $data['bid']) . ')';
			} else {
				$bezirk = 'AND bezirk_id = ' . (int)$data['bid'];
			}
		}

		if (strlen($term) > 2) {
			$out = $this->q('
				SELECT		`id`,
							CONCAT_WS(" ", `name`, `nachname`, CONCAT("(", `id`, ")")) AS value

				FROM 		fs_foodsaver

				WHERE 		((`name` LIKE "' . $term . '%"
				OR 			`nachname` LIKE "' . $term . '%"))
				AND			deleted_at IS NULL
				' . $bezirk . '
			');

			return $out;
		} else {
			return array();
		}
	}

	public function getReg($id)
	{
		return $this->qRow('
				SELECT 	fs.`id` ,
						fs.name,
						fs.nachname,
						fs.anschrift,
						fs.`geschlecht`,
						fs.`photo`,
						fs.`bezirk_id`,
						UNIX_TIMESTAMP(fs.`anmeldedatum`) AS anmeldedatum,
						fs.`data`,
						plz,
						rolle

				FROM 	`fs_foodsaver` fs

				WHERE 	fs.`id` = ' . (int)$id . ';
		');
	}

	public function getEmailAdressen($bezirk_id = false)
	{
		if (!$bezirk_id) {
			$bezirk_id = S::getCurrentBezirkId();
		}

		return $this->q('
				SELECT 	`id`,
						`name`,
						`nachname`,
						`email`,
						`geschlecht`

				FROM 	`fs_foodsaver`

				WHERE 	`bezirk_id` IN(' . implode(',', $this->getChildBezirke($bezirk_id)) . ')
				AND		deleted_at IS NULL
		');
	}

	private function updateHasChildren($bezirk_id)
	{
		$count = $this->qOne('SELECT COUNT(`id`) FROM fs_bezirk WHERE `parent_id` = ' . (int)$bezirk_id . ' ');

		if ($count == 0) {
			$this->update('UPDATE fs_bezirk SET `has_children` = 0 WHERE `id` = ' . (int)$bezirk_id . ' ');
		}
	}

	public function deleteBezirk($id)
	{
		if ($this->func->isOrgaTeam()) {
			$parent_id = $this->getVal('parent_id', 'bezirk', $id);

			$this->update('UPDATE `fs_foodsaver` SET `bezirk_id` = NULL WHERE `bezirk_id` = ' . (int)$id);
			$this->update('UPDATE `fs_bezirk` SET `parent_id` = 0 WHERE `parent_id` = ' . (int)$id);

			$this->del('DELETE FROM `fs_bezirk` WHERE `id` = ' . (int)$id);

			$this->updateHasChildren($parent_id);
		}
	}

	public function addPhoto($fs_id, $file)
	{
		$file = str_replace('/', '', $file);
		$file = strip_tags($file);
		S::setPhoto($file);
		$this->update('

		UPDATE `fs_foodsaver`
		SET 	`photo` = ' . $this->strval($file) . ' WHERE `id` = ' . (int)$fs_id);
	}

	public function getPhoto($fs_id)
	{
		$photo = $this->qOne('SELECT `photo` FROM `fs_foodsaver` WHERE `id` = ' . (int)$fs_id);
		if (!empty($photo)) {
			return $photo;
		}

		return false;
	}

	public function getOne_foodsaver($id)
	{
		$out = $this->qRow('
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

			WHERE 		`id` = ' . (int)$id);

		if ($bot = $this->q('SELECT `fs_bezirk`.`name`,`fs_bezirk`.`id` FROM `fs_bezirk`,fs_botschafter WHERE `fs_botschafter`.`bezirk_id` = `fs_bezirk`.`id` AND `fs_botschafter`.foodsaver_id = ' . (int)$id)) {
			$out['botschafter'] = $bot;
		}

		return $out;
	}

	public function emailExists($email)
	{
		$email = $this->q('SELECT `id` FROM `fs_foodsaver` WHERE `email` = ' . $this->strval($email));

		if (!empty($email)) {
			return true;
		} else {
			return false;
		}
	}

	public function getFoodsaverBasics($fsid)
	{
		if ($fs = $this->qRow('
			SELECT 	fs.`name`,
					fs.nachname,
					fs.bezirk_id,
					fs.rolle,
					fs.photo,
					fs.geschlecht,
					fs.stat_fetchweight,
					fs.sleep_status

			FROM 	`fs_foodsaver` fs

			WHERE fs.id = ' . (int)$fsid . '
		')
		) {
			$fs['bezirk_name'] = '';
			if ($fs['bezirk_id'] > 0) {
				$fs['bezirk_name'] = $this->getVal('name', 'bezirk', $fs['bezirk_id']);
			}

			return $fs;
		}

		return false;
	}

	public function getParentBezirke($bid)
	{
		if (is_array($bid)) {
			$where = 'WHERE bezirk_id IN (' . implode(',', array_map('intval', $bid)) . ')';
		} else {
			$where = 'WHERE bezirk_id = ' . (int)$bid;
		}

		return $this->qCol('SELECT DISTINCT ancestor_id FROM `fs_bezirk_closure` ' . $where);
	}

	/**
	 * @deprecated
	 * @see \Foodsharing\Modules\Region\RegionGateway::listIdsForDescendantsAndSelf()
	 */
	public function getChildBezirke($bid, $nocache = false)
	{
		if ((int)$bid == 0) {
			return false;
		}

		$ou = array();
		$ou[$bid] = $bid;

		if ($out = $this->qCol('SELECT bezirk_id FROM `fs_bezirk_closure` WHERE ancestor_id = ' . (int)$bid)) {
			foreach ($out as $o) {
				$ou[(int)$o] = (int)$o;
			}
		}

		return $ou;
	}

	public function add_foodsaver($data)
	{
		$data['anmeldedatum'] = date('Y-m-d H:i:s');

		if (!isset($data['bezirk_id'])) {
			$data['bezirk_id'] = $this->func->getBezirkId();
		}

		$id = $this->insert('
			INSERT INTO 	`fs_foodsaver`
			(
				`bezirk_id`,
				`plz`,
				`email`,
				`name`,
				`nachname`,
				`anschrift`,
				`telefon`,
				`handy`,
				`geschlecht`,
				`geb_datum`,
				`anmeldedatum`
			)
			VALUES
			(
			' . (int)$data['bezirk_id'] . ',
			' . $this->strval($data['plz']) . ',
			' . $this->strval($data['email']) . ',
			' . $this->strval($data['name']) . ',
			' . $this->strval($data['nachname']) . ',
			' . $this->strval($data['anschrift']) . ',
			' . $this->strval($data['telefon']) . ',
			' . $this->strval($data['handy']) . ',
			' . (int)$data['geschlecht'] . ',
			' . $this->dateval($data['geb_datum']) . ',
			' . $this->dateval($data['anmeldedatum']) . '
			)');

		return $id;
	}

	public function getSendMails()
	{
		return $this->q('
			SELECT 	`name`,
					`message`,
					`zeit`
			FROM 	`fs_send_email`
			WHERE 	`foodsaver_id` = ' . (int)$this->func->fsId() . '
		');
	}

	public function del_foodsaver($id)
	{
		$this->insert('
			INSERT INTO fs_foodsaver_archive
			(
				SELECT * FROM fs_foodsaver WHERE id = ' . (int)$id . '
			)
		');

		$this->del('
            DELETE FROM fs_apitoken
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_application_has_wallpost
            WHERE application_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_basket_anfrage
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_botschafter
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_buddy
            WHERE foodsaver_id = ' . (int)$id . ' OR buddy_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_email_status
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_fairteiler_follower
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_foodsaver_has_bell
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_foodsaver_has_bezirk
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_foodsaver_has_contact
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_foodsaver_has_event
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_foodsaver_has_wallpost
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_mailbox_member
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_mailchange
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_pass_gen
            WHERE foodsaver_id = ' . (int)$id . ' OR bot_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_pass_request
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_quiz_session
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_rating
            WHERE foodsaver_id = ' . (int)$id . '
        ');
		$this->del('
            DELETE FROM fs_theme_follower
            WHERE foodsaver_id = ' . (int)$id . '
		');

		// remove bananas given by this user
		$this->del('
            DELETE FROM fs_rating
            WHERE rater_id = ' . (int)$id . '
		');

		$this->update('UPDATE fs_foodsaver SET verified = 0,
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

	public function getBezirk($id = false)
	{
		if ($id === false) {
			$id = S::getCurrentBezirkId();
		}

		if ($id == 0) {
			return false;
		}

		return $this->qRow('
			SELECT 	`name`,
					`id`,
					`email`,
					`email_name`,
					`has_children`,
					`parent_id`,
					`mailbox_id`

			FROM 	`fs_bezirk`
			WHERE 	`id` = ' . (int)$id);
	}

	public function getEmailsToSend()
	{
		$row = $this->qRow('

				SELECT 	`fs_send_email`.`id`,
						`fs_send_email`.`name`,
						`fs_send_email`.`message`,
						`fs_send_email`.`zeit`,
						COUNT( `fs_email_status`.`foodsaver_id` ) AS `anz`

				FROM 	 `fs_send_email`,
						 `fs_email_status`

				WHERE 	`fs_email_status`.`email_id` =  `fs_send_email`.`id`

				AND 	`fs_send_email`.`foodsaver_id` = ' . (int)$this->func->fsId() . '

				AND 	`fs_email_status`.`status` = 0

			');

		if ($row['anz'] == 0) {
			return false;
		} else {
			return $row;
		}
	}

	public function setEmailStatus($mail_id, $foodsaver, $status)
	{
		$query = '';
		if (is_array($foodsaver)) {
			$query = array();
			foreach ($foodsaver as $fs) {
				$query[] = '`foodsaver_id` = ' . (int)$fs['id'];
			}

			$query = implode(' OR ', $query);
		} else {
			$query = '`foodsaver_id` = ' . (int)$foodsaver;
		}

		return $this->update('
			UPDATE 	`fs_email_status`
			SET 	`status` = ' . (int)$status . '
			WHERE 	`email_id` = ' . (int)$mail_id . '
			AND 	(' . $query . ')
		');
	}

	public function getMailsLeft($mail_id)
	{
		return $this->qOne('SELECT COUNT(`email_id`) FROM `fs_email_status` WHERE `email_id` = ' . (int)$mail_id . ' AND `status` = 0');
	}

	public function getMailNext($mail_id)
	{
		return $this->q('
			SELECT
			s.`email_id`,
			fs.`id`,
			s.`status`,
			fs.`name`,
			fs.`geschlecht`,
			fs.`email`,
			fs.`token`

			FROM 		`fs_email_status` s,
						`fs_foodsaver` fs

			WHERE 		fs.`id` = s.`foodsaver_id`
			AND 		s.email_id = ' . (int)$mail_id . '

			AND 		s.`status` = 0

			LIMIT 10
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

		$foodsaver = $this->q('
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

		$foodsaver = $this->q('
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

	public function initEmail($mailbox_id, $foodsaver, $message, $subject, $attach, $mode)
	{
		if ((int)$mailbox_id == 0) {
			return false;
		}

		$attach_db = '';
		if ($attach !== false) {
			$attach_db = json_encode(array($attach));
		}

		if (!$this->func->isOrgaTeam()) {
			$mode = 1;
		}

		$data = array(
			'mailbox_id' => $mailbox_id,
			'subject' => $subject,
			'message' => $message,
			'attach' => $attach_db,
			'mode' => $mode
		);

		$email_id = $this->add_sendMail($data);

		$query = array();
		foreach ($foodsaver as $fs) {
			$query[] = '(' . (int)$email_id . ',' . (int)$fs['id'] . ',0)';
		}

		if ($this->func->isAdmin()) {
		}
		/*
		 * Array
		(
			[0] => (33,56,0)
			[1] => (33,146,0)
		)
		 */
		$this->sql('
			INSERT INTO `fs_email_status` (`email_id`,`foodsaver_id`,`status`)
			VALUES
			' . implode(',', $query) . ';
		');
	}

	public function getOne_send_email($id)
	{
		$out = $this->qRow('
			SELECT
			`id`,
			`foodsaver_id`,
			`mailbox_id`,
			`mode`,
			`complete`,
			`name`,
			`message`,
			`zeit`,
			`recip`,
			`attach`

			FROM 		`fs_send_email`

			WHERE 		`id` = ' . (int)$id);

		return $out;
	}

	private function add_sendMail($data)
	{
		if (!isset($data['mode'])) {
			$data['mode'] = 1;
		}

		return $this->insert('
				INSERT INTO 	fs_send_email (foodsaver_id, mailbox_id, name,`mode`, message, zeit, `attach`)

				VALUES(
					' . (int)$this->func->fsId() . ',
					' . (int)$data['mailbox_id'] . ',
					' . $this->strval($data['subject']) . ',
					' . (int)$data['mode'] . ',
					' . $this->strval($data['message'], true) . ',
					' . $this->dateval(date('Y-m-d H:i:s')) . ',
					' . $this->strval($data['attach']) . '
				)

		');
	}

	public function getMailBezirk($id)
	{
		return $this->qRow('
			SELECT
			`id`,
			`name`,
			`email`,
			`email_name`,
			`email_pass`

			FROM 		`fs_bezirk`

			WHERE 		`id` = ' . (int)$id);
	}

	public function getMailboxname($mailbox_id)
	{
		return $this->qOne('SELECT `name` FROM fs_mailbox WHERE id = ' . (int)$mailbox_id);
	}

	public function getOne_bezirk($id)
	{
		$out = $this->qRow('
			SELECT
			`id`,
			`parent_id`,
			`has_children`,
			`name`,
			`email`,
			`email_pass`,
			`email_name`,
			`type`,
			`master`,
			`mailbox_id`

			FROM 		`fs_bezirk`

			WHERE 		`id` = ' . (int)$id);
		$out['botschafter'] = $this->q('
				SELECT 		`fs_foodsaver`.`id`,
							CONCAT(`fs_foodsaver`.`name`," ",`fs_foodsaver`.`nachname`) AS name

				FROM 		`fs_botschafter`,
							`fs_foodsaver`

				WHERE 		`fs_foodsaver`.`id` = `fs_botschafter`.`foodsaver_id`
				AND 		`fs_botschafter`.`bezirk_id` = ' . (int)$id . '
			');

		$out['foodsaver'] = $this->qCol('
				SELECT 		`foodsaver_id`

				FROM 		`fs_botschafter`
				WHERE 		`bezirk_id` = ' . (int)$id . '
			');

		return $out;
	}

	public function getBasics_foodsaver($bezirk_id = false)
	{
		if (!$bezirk_id) {
			$bezirk_id = S::getCurrentBezirkId();
		}

		return $this->q('
			SELECT 	 	`id`,
						CONCAT(`name`," ",`nachname`) AS `name`,
						`anschrift`

			FROM 		`fs_foodsaver`

			WHERE 		`bezirk_id` = ' . (int)$bezirk_id . '
			AND			deleted_at IS NULL

			ORDER BY `name`');
	}

	public function update_bezirkNew($id, $data)
	{
		$bezirk_id = (int)$id;
		if (isset($data['botschafter']) && is_array($data['botschafter'])) {
			$this->del('
					DELETE FROM 	`fs_botschafter`
					WHERE 			`bezirk_id` = ' . (int)$id . '
				');
			$master = 0;
			if (isset($data['master'])) {
				$master = (int)$data['master'];
			}
			foreach ($data['botschafter'] as $foodsaver_id) {
				$this->insert('
						INSERT INTO `fs_botschafter`
						(
							`bezirk_id`,
							`foodsaver_id`
						)
						VALUES
						(
							' . (int)$id . ',
							' . (int)$foodsaver_id . '
						)
					');
			}
		}

		$this->begin_transaction();

		if ((int)$data['parent_id'] > 0) {
			$this->update('UPDATE `fs_bezirk` SET `has_children` = 1 WHERE `id` = ' . (int)$data['parent_id']);
		}

		$has_children = 0;
		if ($this->q('
			SELECT	id FROM fs_bezirk WHERE parent_id = ' . (int)$id . '
		')
		) {
			$has_children = 1;
		}

		Mem::del('cb-' . $id);

		$this->update('
		UPDATE 	`fs_bezirk`

		SET 	`name` =  ' . $this->strval($data['name']) . ',
				`email_name` =  ' . $this->strval($data['email_name']) . ',
				`parent_id` = ' . (int)$data['parent_id'] . ',
				`type` = ' . (int)$data['type'] . ',
				`master` = ' . (int)$master . ',
				`has_children` = ' . (int)$has_children . '

		WHERE 	`id` = ' . (int)$id);

		$this->sql('DELETE a FROM `fs_bezirk_closure` AS a JOIN `fs_bezirk_closure` AS d ON a.bezirk_id = d.bezirk_id LEFT JOIN `fs_bezirk_closure` AS x ON x.ancestor_id = d.ancestor_id AND x.bezirk_id = a.ancestor_id WHERE d.ancestor_id = ' . (int)$bezirk_id . ' AND x.ancestor_id IS NULL');
		$this->sql('INSERT INTO `fs_bezirk_closure` (ancestor_id, bezirk_id, depth) SELECT supertree.ancestor_id, subtree.bezirk_id, supertree.depth+subtree.depth+1 FROM `fs_bezirk_closure` AS supertree JOIN `fs_bezirk_closure` AS subtree WHERE subtree.ancestor_id = ' . (int)$bezirk_id . ' AND supertree.bezirk_id = ' . (int)(int)$data['parent_id']);
		$this->commit();
	}

	public function update_blog_entry($id, $data)
	{
		$pic = '';
		if (!empty($data['picture'])) {
			$pic = ',`picture` =  ' . $this->strval($data['picture']);
		}

		return $this->update('
		UPDATE 	`fs_blog_entry`

		SET 	`bezirk_id` =  ' . (int)$data['bezirk_id'] . ',
				`foodsaver_id` =  ' . (int)$data['foodsaver_id'] . ',
				`name` =  ' . $this->strval($data['name']) . ',
				`teaser` =  ' . $this->strval($data['teaser']) . ',
				`body` =  ' . $this->strval($data['body'], true) . ',
				`time` =  ' . $this->dateval($data['time']) . '
				' . $pic . '

		WHERE 	`id` = ' . (int)$id);
	}

	public function update_bezirk($id, $data)
	{
		if (isset($data['foodsaver']) && is_array($data['foodsaver'])) {
			$this->del('
					DELETE FROM 	`fs_botschafter`
					WHERE 			`bezirk_id` = ' . (int)$id . '
				');

			foreach ($data['foodsaver'] as $foodsaver_id) {
				$this->insert('
						INSERT INTO `fs_botschafter`
						(
							`bezirk_id`,
							`foodsaver_id`
						)
						VALUES
						(
							' . (int)$id . ',
							' . (int)$foodsaver_id . '
						)
					');
			}
		}

		Mem::del('cb-' . $id);

		return $this->update('
		UPDATE 	`fs_bezirk`

		SET 	`name` =  ' . $this->strval($data['name']) . '

		WHERE 	`id` = ' . (int)$id);
	}

	public function getBotschafter($bezirk_id)
	{
		return $this->q('

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

				AND `fs_botschafter`.`bezirk_id` = ' . (int)$bezirk_id . '
				AND		fs.deleted_at IS NULL
		');
	}

	public function getAllBotschafter()
	{
		return $this->q('

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
				AND		fs.deleted_at IS NULL
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
		return $this->q('

				SELECT 		`id`,
							`name`,
							`nachname`,
							`geschlecht`,
							`email`

				FROM 		`fs_foodsaver`

				WHERE 		`orgateam` = 1

		');
	}

	public function updatePhoto($fs_id, $photo)
	{
		return $this->update('
				UPDATE `fs_foodsaver`
				SET 	`photo` = ' . $this->strval($photo) . '
				WHERE 	`id` = ' . (int)$fs_id . '');
	}

	public function updateProfile($fs_id, $data)
	{
		if (!isset($data['bezirk_id'])) {
			$data['bezirk_id'] = $this->func->getBezirkId();
		}
		if (!isset($data['photo_public'])) {
			$data['photo_public'] = 0;
		}

		$sql = '
		UPDATE 	`fs_foodsaver`

		SET
				`bezirk_id` =  ' . (int)$data['bezirk_id'] . ',
				`plz` =  ' . $this->strval($data['plz']) . ',
				`lat` =  ' . $this->strval($data['lat']) . ',
				`lon` =  ' . $this->strval($data['lon']) . ',
				`stadt` =  ' . $this->strval($data['stadt']) . ',

				`anschrift` =  ' . $this->strval($data['anschrift']) . ',
				`telefon` =  ' . $this->strval($data['telefon']) . ',
				`handy` =  ' . $this->strval($data['handy']) . ',
				`geb_datum` =  ' . $this->dateval($data['geb_datum']) . ',

				`about_me_public` =  ' . $this->strval($data['about_me_public']) . ',
				`photo_public` = ' . (int)$data['photo_public'] . ',
				`homepage` = ' . $this->strval($data['homepage']) . ',';
		if (isset($data['twitter'])) {
			$sql .= '`twitter` = ' . $this->strval($data['twitter']) . ',';
		}
		if (isset($data['github'])) {
			$sql .= '`github` = ' . $this->strval($data['github']) . ',';
		}
		if (isset($data['position'])) {
			$sql .= '`position` = ' . $this->strval($data['position']) . ',';
		}
		$sql .= '`tox` = ' . $this->strval($data['tox']) . '

		WHERE 	`id` = ' . (int)$fs_id;

		//debug($sql);

		if ($this->update($sql)) {
			$this->relogin();

			return true;
		}
	}

	public function getWantNew($bezirk_id)
	{
		$onlybot = '';

		if (!$this->func->isOrgaTeam()) {
			$bid = S::getBotBezirkIds();
			$onlybot = 'AND (	`bezirk_id` = ' . implode(' OR `bezirk_id` = ', $bid) . ' )	';
		}

		return $this->q('
			SELECT 	`fs_foodsaver`.`id`,
					`fs_foodsaver`.`name`,
					`fs_foodsaver`.`photo`,
					`fs_foodsaver`.`nachname`,
					`fs_foodsaver`.`new_bezirk`,
					`fs_foodsaver`.`bezirk_id`,
					`fs_bezirk`.`name` AS bezirk_name
			FROM 	`fs_foodsaver` fs,
					`fs_bezirk`

			WHERE 	`fs_foodsaver`.`bezirk_id` = `fs_bezirk`.`id`

			AND 	`want_new` = 1
			AND		fs.deleted_at IS NULL

			' . $onlybot . '

			ORDER BY `bezirk_id`,`bezirk_name`
		');
	}

	public function getNextEvents()
	{
		$next = $this->q('
			SELECT
				e.id,
				e.name,
				e.`description`,
				e.`start`,
				UNIX_TIMESTAMP(e.`start`) AS start_ts,
				fe.`status`

			FROM
				`fs_event` e
			LEFT JOIN
				`fs_foodsaver_has_event` fe
			ON
				e.id = fe.event_id AND fe.foodsaver_id = ' . (int)$this->func->fsId() . '

			WHERE
				e.start >= CURDATE()
			AND
				((e.public = 1 AND (fe.`status` IS NULL OR fe.`status` <> 3))
				OR
					fe.`status` IN(1,2)
				)
			ORDER BY e.`start`
		');

		$out = array();

		if ($next) {
			foreach ($next as $n) {
				$out[date('Y-m-d H:i', $n['start_ts']) . '-' . $n['id']] = $n;
			}
		}
		if (!empty($out)) {
			return $out;
		}
	}

	public function getInvites()
	{
		return $this->q('
			SELECT
				e.id,
				e.name,
				e.`description`,
				e.`start`,
				UNIX_TIMESTAMP(e.`start`) AS start_ts,
				fe.`status`

			FROM
				`fs_event` e,
				`fs_foodsaver_has_event` fe

			WHERE
				fe.event_id = e.id

			AND
				fe.foodsaver_id = ' . (int)$this->func->fsId() . '

			AND
				fe.`status` = 0

			AND
				e.`end` > NOW()
		');
	}

	public function getOne_message_tpl($id)
	{
		$out = $this->qRow('
			SELECT
			`id`,
			`language_id`,
			`name`,
			`subject`,
			`body`

			FROM 		`fs_message_tpl`

			WHERE 		`id` = ' . (int)$id);

		return $out;
	}

	public function getBasics_bezirk()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`

			FROM 		`fs_bezirk`
			ORDER BY `name`');
	}

	public function get_faq()
	{
		$out = $this->q('
			SELECT
			`id`,
			`foodsaver_id`,
			`faq_kategorie_id`,
			`name`,
			`answer`

			FROM 		`fs_faq`
			ORDER BY `name`');

		return $out;
	}

	public function getOne_faq($id)
	{
		$out = $this->qRow('
			SELECT
			`id`,
			`foodsaver_id`,
			`faq_kategorie_id`,
			`name`,
			`answer`

			FROM 		`fs_faq`

			WHERE 		`id` = ' . (int)$id);

		return $out;
	}

	public function getBasics_faq_category()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`

			FROM 		`fs_faq_category`
			ORDER BY `name`');
	}

	public function update_faq($id, $data)
	{
		return $this->update('
		UPDATE 	`fs_faq`

		SET 	`foodsaver_id` =  ' . (int)$data['foodsaver_id'] . ',
				`faq_kategorie_id` =  ' . (int)$data['faq_kategorie_id'] . ',
				`name` =  ' . $this->strval($data['name']) . ',
				`answer` =  ' . $this->strval($data['answer']) . '

		WHERE 	`id` = ' . (int)$id);
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

		return $this->qCol('SELECT DISTINCT bot.foodsaver_id FROM `fs_bezirk_closure` c
			LEFT JOIN `fs_bezirk` bz ON bz.id = c.bezirk_id
			INNER JOIN `fs_botschafter` bot ON bot.bezirk_id = c.bezirk_id
			INNER JOIN `fs_foodsaver` fs ON fs.id = bot.foodsaver_id
			WHERE c.ancestor_id = ' . (int)$bezirk . ' AND fs.deleted_at IS NULL AND ' . $where_type);
	}

	/* updates the member list to given list of IDs, optionally leaving admins
		that are not in the list in place */
	public function updateGroupMembers($bezirk, $foodsaver_ids, $leave_admins)
	{
		$rows_ins = 0;
		$rows_del = 0;
		if ($leave_admins) {
			$admins = $this->qCol('SELECT foodsaver_id FROM `fs_botschafter` b WHERE b.bezirk_id = ' . (int)$bezirk);
			if ($admins) {
				$foodsaver_ids = array_merge($foodsaver_ids, $admins);
			}
		}
		$ids = implode(',', array_map('intval', $foodsaver_ids));
		if ($ids) {
			$rows_del = $this->del('DELETE FROM `fs_foodsaver_has_bezirk` WHERE bezirk_id = ' . (int)$bezirk . ' AND foodsaver_id NOT IN (' . $ids . ')');
			$insert_strings = array_map(function ($id) use ($bezirk) {
				return '(' . $id . ',' . $bezirk . ',1,NOW())';
			}, $foodsaver_ids);
			$insert_values = implode(',', $insert_strings);
			$rows_ins = $this->del('INSERT IGNORE INTO `fs_foodsaver_has_bezirk` (foodsaver_id, bezirk_id, active, added) VALUES ' . $insert_values);
		} else {
			$rows_del = $this->del('DELETE FROM `fs_foodsaver_has_bezirk` WHERE bezirk_id = ' . (int)$bezirk);
		}

		return array($rows_ins, $rows_del);
	}
}
