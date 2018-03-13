<?php

namespace Foodsharing\Lib\Db;

use Exception;
use Flourish\fImage;
use Foodsharing\Debug\DebugBar;
use Foodsharing\Lib\Func;
use Foodsharing\Lib\Session\S;
use mysqli;

abstract class Db
{
	/**
	 * @var mysqli
	 */
	private $mysqli;
	private $values;
	/**
	 * @var Func
	 */
	protected $func;

	public function __construct()
	{
		$this->values = array();
	}

	/**
	 * @required
	 */
	public function setFunc(Func $func)
	{
		$this->func = $func;
	}

	/**
	 * @required
	 */
	public function setMysqli(mysqli $mysqli)
	{
		$this->mysqli = $mysqli;
	}

	public function addPassRequest($email, $mail = true)
	{
		if ($fs = $this->qRow('SELECT fs.`id`,fs.`email`,fs.`name`,fs.`geschlecht` FROM `fs_foodsaver` fs WHERE fs.deleted_at IS NULL AND fs.`email` = ' . $this->strval($email))) {
			$k = uniqid();
			$key = md5($k);

			$this->insert('
			REPLACE INTO 	`fs_pass_request`
			(
				`foodsaver_id`,
				`name`,
				`time`
			)
			VALUES
			(
				' . $this->intval($fs['id']) . ',
				' . $this->strval($key) . ',
				NOW()
			)');

			if ($mail) {
				$vars = array(
					'link' => BASE_URL . '/?page=login&sub=passwordReset&k=' . $key,
					'name' => $fs['name'],
					'anrede' => $this->func->genderWord($fs['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r')
				);

				$this->func->tplMail(10, $fs['email'], $vars);

				return true;
			} else {
				return $key;
			}
		}

		return false;
	}

	public function addComment($data)
	{
		$out = false;
		if (isset($data['name'])) {
			switch ($data['name']) {
				case 'betrieb':
					$out = $this->addCommentBetrieb($data);
					break;
				default:
					return false;
					break;
			}
		}

		if ($out !== false) {
			return $out;
		} else {
			return '0';
		}
	}

	private function addCommentBetrieb($data)
	{
		if ((int)$data['id'] > 0 && strlen($data['comment']) > 0) {
			$this->insert('
			INSERT INTO 	`fs_betrieb_notiz`
			(
				`foodsaver_id`,
				`betrieb_id`,
				`text`,
				`zeit`
			)
			VALUES
			(
				' . $this->getFoodsaverId() . ',
				' . $this->intval($data['id']) . ',
				' . $this->strval(urldecode($data['comment'])) . ',
				' . $this->dateval(date('Y-m-d H:i:s')) . '
			)');

			return json_encode(array(
				'status' => 1,
				'msg' => 'Notiz wurde gespeichert!'
			));
		}

		return false;
	}

	public function getRolle()
	{
		$out = array();
		if (isset($_SESSION['client']['botschafter'])) {
			if ($name = $this->getBezirkName($_SESSION['client']['botschafter'][0]['bezirk_id'])) {
				$out['botschafter'] = array(
					'bezirk_id' => (int)$_SESSION['client']['botschafter'][0]['bezirk_id'],
					'bezirk_name' => $name
				);
			}
		}

		if (isset($_SESSION['client']['verantwortlich'])) {
			$out['verantwortlich'] = array();

			$i = 0;
			foreach ($_SESSION['client']['verantwortlich'] as $v) {
				if ($name = $this->getBetriebName($v['betrieb_id'])) {
					$out['verantwortlich'][] = array(
						'betrieb_id' => (int)$v['betrieb_id'],
						'betrieb_name' => $name
					);
				}
				++$i;
				if ($i == 1000) {
					break;
				}
			}
		}

		if (empty($out)) {
			return false;
		} else {
			return $out;
		}
	}

	public function getBezirkName($bezirk_id = false)
	{
		if ($bezirk_id === false) {
			$bezirk_id = $this->getCurrentBezirkId();
		}

		return $this->qOne('SELECT `name` FROM `fs_bezirk` WHERE `id` = ' . $this->intval($bezirk_id));
	}

	private function getBetriebName($betrieb_id)
	{
		return $this->qOne('SELECT `name` FROM `fs_betrieb` WHERE `id` = ' . $this->intval($betrieb_id));
	}

	public function addBetrieb($data)
	{
		/*
		 * Array
(
	[form_submit] => newbetrieb
	[bezeichnung] => kjl
	[str] =>
	[hsnr] =>
	[plz] => 123
	[ort] =>
	[kategorie] => 2
	[status] => 2
	[betreibskette] => 3
	[ansprechpartner] =>
	[emailadresse] =>
	[telefon] =>
	[fax] =>
	[verantwortlicherfoodsaver] =>
)
		 */

		if ($betrieb_id = $this->insert('
				INSERT INTO fs_betrieb
				(
					plz,
					bezirk_id,
					kette_id,
					betrieb_kategorie_id,
					name,
					str,
					hsnr,
					`status`,
					status_date,
					ansprechpartner,
					telefon,
					email,
					fax
				)
				VALUES
				(
					' . $this->strval($data['plz']) . ',
					' . $this->intval($data['bezirk_id']) . ',
					' . $this->intval($data['betreibskette']) . ',
					' . $this->intval($data['kategorie']) . ',
					' . $this->strval($data['bezeichnung']) . ',
					' . $this->strval($data['str']) . ',
					' . $this->strval($data['hsnr']) . ',
					' . $this->intval($data['status']) . ',
					' . $this->dateval(date('Y-m-d')) . ',
					' . $this->strval($data['ansprechpartner']) . ',
					' . $this->strval($data['telefon']) . ',
					' . $this->strval($data['emailadresse']) . ',
					' . $this->strval($data['fax']) . '

				)
		')
		) {
			if (!empty($data['verantwortlicherfoodsaver'])) {
				$this->addVerantwortlicher($data['verantwortlicherfoodsaver'], $betrieb_id);
			}

			return $betrieb_id;
		}

		return false;
	}

	public function getBezirkId($name)
	{
		if ($id = $this->qOne('SELECT `id` FROM `fs_bezirk` WHERE `name` = ' . $this->strval($name))) {
			return $id;
		} else {
			return $this->insert('INSERT INTO `fs_bezirk`(`name`)VALUES(' . $this->strval($name) . ')');
		}
	}

	public function getAllFoodsaver()
	{
		return $this->q('
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

	public function getBetriebe($bezirk_id = false)
	{
		if (!$bezirk_id) {
			$bezirk_id = $this->getCurrentBezirkId();
		}

		return $this->q('
				SELECT 	fs_betrieb.id,
						fs_betrieb.plz,
						fs_betrieb.kette_id,
						fs_betrieb.betrieb_kategorie_id,
						fs_betrieb.name,
						fs_betrieb.str,
						fs_betrieb.hsnr,
						fs_betrieb.`betrieb_status_id`

				FROM 	fs_betrieb

				WHERE 	fs_betrieb.bezirk_id = ' . $this->intval($bezirk_id) . '


				'); // -- AND 	'.PREFIX.'betrieb.bezirk_id = '.$this->intval(1).'
	}

	public function may()
	{
		if (isset($_SESSION) && isset($_SESSION['client']) && (int)$_SESSION['client']['id'] > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function begin_transaction()
	{
		$this->mysqli->query('BEGIN');
	}

	public function commit()
	{
		$this->mysqli->commit();
	}

	public function rollback()
	{
		$this->mysqli->rollback();
	}

	public function sql($query)
	{
		$start = microtime(true);
		$res = $this->mysqli->query($query);
		$duration = microtime(true) - $start;

		if ($res == false) {
			error_log('SQL QUERY ERROR URL ' . $_SERVER['REQUEST_URI'] . ' IN ' . $query . ' : ' . $this->mysqli->error);
			DebugBar::addQuery($query, $duration, false, $this->mysqli->errno, $this->mysqli->error);
		} else {
			DebugBar::addQuery($query, $duration, true);
		}

		return $res;
	}

	/**
	 * @deprecated use db->fetchValue
	 */
	public function qOne($sql)
	{
		if ($res = $this->sql($sql)) {
			if ($row = $res->fetch_array()) {
				if (isset($row[0])) {
					return $this->func->qs($row[0]);
				}
			}
		}

		return false;
	}

	/**
	 * @deprecated use db->fetchAllValues
	 */
	public function qCol($sql)
	{
		$out = array();
		if ($res = $this->sql($sql)) {
			while ($row = $res->fetch_array()) {
				$out[] = $this->func->qs($row[0]);
			}
		}

		return $out;
	}

	/**
	 * Method to get an asoc array insted the colums are the keys
	 * so aftter all we can check like this if(isset($test[$key])) ...
	 *
	 * @param string $sql
	 *
	 * @return array |boolean
	 *
	 * @deprecated use db->fetchAllValues and adapt code to not use indexed array
	 */
	public function qColKey($sql)
	{
		$out = array();
		if ($res = $this->sql($sql)) {
			while ($row = $res->fetch_array()) {
				$val = (int)($row[0]);
				$out[$val] = $val;
			}
		}

		if (count($out) > 0) {
			return $out;
		} else {
			return false;
		}
	}

	/**
	 * @deprecated use db->fetch
	 */
	public function qRow($sql)
	{
		try {
			$res = $this->sql($sql);

			if (is_object($res) && ($row = $res->fetch_assoc())) {
				foreach ($row as $i => $r) {
					$row[$i] = $this->func->qs($r);
				}

				return $row;
			}
		} catch (Exception $e) {
		}

		return false;
	}

	/**
	 * @deprecated use db->delete
	 */
	public function del($sql)
	{
		if ($res = $this->sql($sql)) {
			return $this->mysqli->affected_rows;
		}

		return false;
	}

	/**
	 * @deprecated use db->insert
	 */
	public function insert($sql)
	{
		if ($res = $this->sql($sql)) {
			return $this->mysqli->insert_id;
		} else {
			return false;
		}
	}

	/**
	 * @deprecated cast to int if necessary
	 */
	public function intval($val)
	{
		return (int)$val;
	}

	/**
	 * @deprecated use db->update
	 */
	public function update($sql)
	{
		if ($this->sql($sql)) {
			return true;
		} else {
			return false;
		}
	}

	public function dateval($val)
	{
		return '"' . $this->safe($val) . '"';
	}

	/**
	 * @deprecated use floatval() directly
	 */
	public function floatval($val)
	{
		return floatval($val);
	}

	/**
	 * @deprecated use strip_tags() until the frontend can escape properly.
	 * String escaping is not needed anymore with prepared statements
	 */
	public function strval($val, $html = false)
	{
		if (is_string($html) || $html === false) {
			if (is_string($html)) {
				$val = strip_tags($val, $html);
			} else {
				$val = strip_tags($val);
			}
		}

		return '"' . $this->safe($val) . '"';
	}

	/**
	 * @deprecated use db->fetchAll
	 */
	public function q($sql)
	{
		$out = array();
		if ($res = $this->sql($sql)) {
			while ($row = $res->fetch_assoc()) {
				foreach ($row as $i => $r) {
					$row[$i] = $this->func->qs($r);
				}
				$out[] = $row;
			}
		}

		return $out;
	}

	public function __destruct()
	{
	}

	public function close()
	{
		@$this->mysqli->close();
		$this->mysqli = null;
	}

	public function safe($str)
	{
		return $this->mysqli->escape_string($str);
	}

	public function getFoodsaverId()
	{
		return (int)$_SESSION['client']['id'];
	}

	public function relogin()
	{
		$this->initSessionData($_SESSION['client']['id']);

		return true;
	}

	public function logout()
	{
		Mem::userDel($this->func->fsId(), 'active');
		Mem::userDel($this->func->fsId(), 'lastMailMessage');
		Mem::userRemoveSession($this->func->fsId(), session_id());
	}

	public function login($email, $pass)
	{
		$email = trim($email);
		if ($this->qOne('
			SELECT email FROM `fs_email_blacklist`
			WHERE email = ' . $this->strval($email))) {
			return false;
		}
		if ($fsid = $this->checkClient($email, $pass)) {
			$this->initSessionData($fsid);

			$this->update('
				UPDATE fs_foodsaver
				SET 	last_login = NOW()
				WHERE 	id = ' . (int)$this->func->fsId() . '
			');

			return true;
		} else {
			return false;
		}
	}

	public function gerettet_wrapper($id)
	{
		$ger = array(
			1 => 2,
			2 => 4,
			3 => 7.5,
			4 => 15,
			5 => 25,
			6 => 45,
			7 => 64
		);

		if (!isset($ger[$id])) {
			return 1.5;
		}

		return $ger[$id];
	}

	/**
	 * hashes password with modern hashing algorithmn.
	 */
	public function password_hash($password)
	{
		return password_hash($password, PASSWORD_ARGON2I);
	}

	/**
	 * Generates md5 hash with email as salt. used before
	 * xx.02.2018.
	 */
	public function encryptMd5($email, $pass)
	{
		$email = strtolower($email);

		return md5($email . '-lz%&lk4-' . $pass);
	}

	/**
	 * Generate a foodsharing.de style hash before 12.12.2014
	 * fusion.
	 * Uses sha1 of concatenation of fixed salt and password.
	 */
	private function fs_sha1hash($pass)
	{
		$salt = 'DYZG93b04yJfIxfs2guV3Uub5wv7iR2G0FgaC9mi';

		return sha1($salt . $pass);
	}

	/**
	 * Check given email and password combination,
	 * update password if old-style one is detected.
	 */
	public function checkClient($email, $pass = false)
	{
		$email = $this->safe(trim($email));
		if (strlen($email) < 2 || strlen($pass) < 1) {
			return false;
		}

		$user = $this->qRow('
			SELECT 	`id`,
					`password`,
					`passwd`,
					`fs_password`,
					`bezirk_id`,
					`admin`,
					`orgateam`,
					`photo`

			FROM 	`fs_foodsaver`
			WHERE 	`email`     = "' . $email . '"
			AND     `deleted_at`   IS NULL
		');

		// does the email exist?
		if (!$user) {
			return false;
		}

		// modern hashing algorithm
		if ($user['password']) {
			if (password_verify($pass, $user['password'])) {
				return $user['id'];
			} else {
				return false;
			}

			// old hashing algorithm
		} else {
			if (
				($user['passwd'] && $user['passwd'] == $this->encryptMd5($email, $pass)) || // md5
				($user['fs_password'] && $user['fs_password'] == $this->fs_sha1hash($pass))  // sha1
			) {
				// update stored password to modern
				$this->update('UPDATE `fs_' . "foodsaver` 
					SET `fs_password` = NULL, `passwd` = NULL, `password` = '" . $this->password_hash($pass) . "'
					WHERE `id` = " . $user['id']
				);

				return $user['id'];
			} else {
				return false;
			}
		}
	}

	/**
	 * Method to check users online status by checking timestamp from memcahce.
	 *
	 * @param int $fs_id
	 *
	 * @return bool
	 */
	public function isActive($fs_id)
	{
		if ($time = Mem::user($fs_id, 'active')) {
			return !((time() - $time) > 600);
		}

		return false;
	}

	public function updateActivity($fs_id = null)
	{
		if ($fs_id) {
			Mem::userSet($fs_id, 'active', time());
		}
	}

	private function initSessionData($fs_id)
	{
		$this->updateActivity($fs_id);
		if ($fs = $this->qRow('
				SELECT 		`id`,
							`admin`,
							`orgateam`,
							`bezirk_id`,
							`photo`,
							`rolle`,
							`type`,
							`verified`,
							`name`,
							`nachname`,
							`lat`,
							`lon`,
							`email`,
							`token`,
							`mailbox_id`,
							`option`,
							`geschlecht`

				FROM 		`fs_foodsaver`

				WHERE 		`id` = ' . $this->intval($fs_id) . '
		')
		) {
			S::set('g_location', array(
				'lat' => $fs['lat'],
				'lon' => $fs['lon']
			));

			/*
			 * temporary special stuff for quiz
			 */
			$hastodo = false;
			$hastodo_id = 0;

			$count_fs_quiz = (int)$this->qOne('SELECT COUNT(id) FROM fs_quiz_session WHERE foodsaver_id = ' . (int)$fs_id . ' AND quiz_id = 1 AND `status` = 1');
			$count_bib_quiz = (int)$this->qOne('SELECT COUNT(id) FROM fs_quiz_session WHERE foodsaver_id = ' . (int)$fs_id . ' AND quiz_id = 2 AND `status` = 1');
			$count_bot_quiz = (int)$this->qOne('SELECT COUNT(id) FROM fs_quiz_session WHERE foodsaver_id = ' . (int)$fs_id . ' AND quiz_id = 3 AND `status` = 1');

			$count_verantwortlich = (int)$this->qOne('SELECT COUNT(betrieb_id) FROM fs_betrieb_team WHERE foodsaver_id = ' . (int)$fs_id . ' AND verantwortlich = 1');
			$count_botschafter = (int)$this->qOne('SELECT COUNT( bezirk_id )FROM fs_botschafter WHERE foodsaver_id = ' . (int)$fs_id);

			$quiz_rolle = 0;
			if ($count_fs_quiz > 0) {
				$quiz_rolle = 1;
			}
			if ($count_bib_quiz > 0) {
				$quiz_rolle = 2;
			}
			if ($count_bot_quiz > 0) {
				$quiz_rolle = 3;
			}

			$this->update('UPDATE fs_foodsaver SET quiz_rolle = ' . (int)$quiz_rolle . ' WHERE id = ' . (int)$fs['id']);
			/*
			echo '<pre>';
			echo $count_verantwortlich."\n";
			echo $count_fs_quiz;
			die();
			*/

			if ((int)$fs['rolle'] == 1 && $count_fs_quiz == 0) {
				$hastodo = true;
				$hastodo_id = 1;
			} elseif (
				(
					(int)$fs['rolle'] > 1 || $count_verantwortlich > 0
				)
				&&
				$count_bib_quiz === 0
			) {
				$hastodo = true;
				$hastodo_id = 2;
			} elseif (
				(
					(int)$fs['rolle'] > 2 || $count_botschafter > 0
				)
				&&
				$count_bot_quiz === 0
			) {
				$hastodo = true;
				$hastodo_id = 3;
			}

			S::set('hastodoquiz', $hastodo);
			S::set('hastodoquiz-id', $hastodo_id);

			/*
			 * temp quiz stuff end...
			 */

			$mailbox = false;
			if ((int)$fs['mailbox_id'] > 0) {
				$mailbox = true;
			}
			if ((int)$fs['bezirk_id'] > 0 && $fs['rolle'] > 0) {
				$this->insert('
					INSERT IGNORE INTO `fs_foodsaver_has_bezirk`(`foodsaver_id`, `bezirk_id`, `active`, `added`) VALUES
					(' . (int)$fs['id'] . ',' . (int)$fs['bezirk_id'] . ',1,NOW())
				');
			}

			if ($master = $this->getVal('master', 'bezirk', $fs['bezirk_id'])) {
				$this->insert('
					INSERT IGNORE INTO `fs_foodsaver_has_bezirk`(`foodsaver_id`, `bezirk_id`, `active`, `added`) VALUES
					(' . (int)$fs['id'] . ',' . (int)$master . ',1,NOW())
				');
			}

			if ($fs['photo'] != '' && file_exists('images/mini_q_' . $fs['photo'])) {
				$image1 = new fImage('images/mini_q_' . $fs['photo']);
				if ($image1->getWidth() > 36) {
					$image1->cropToRatio(1, 1);
					$image1->resize(35, 35);
					$image1->saveChanges();
				}
			}

			$fs['buddys'] = $this->qColKey('SELECT buddy_id FROM fs_buddy WHERE foodsaver_id = ' . (int)$fs_id . ' AND confirmed = 1');

			/*
			 * New Session Management
			 */
			S::login($fs);

			/*
			 * Add entry into user -> session set
			 */
			Mem::userAddSession($fs_id, session_id());

			/*
			 * store all options in the session
			*/

			if (!empty($fs['option'])) {
				$options = unserialize($fs['option']);
				foreach ($options as $key => $val) {
					S::setOption($key, $val, $this);
				}
			}

			$_SESSION['login'] = true;
			$_SESSION['client'] = array(
				'id' => $fs['id'],
				'bezirk_id' => $fs['bezirk_id'],
				'group' => array('member' => true),
				'photo' => $fs['photo'],
				'rolle' => (int)$fs['rolle'],
				'verified' => (int)$fs['verified']
			);
			if ($fs['admin'] == 1) {
				$_SESSION['client']['group']['admin'] = true;
			}
			if ($fs['orgateam'] == 1) {
				$_SESSION['client']['group']['orgateam'] = true;
			}
			if ((int)$fs['rolle'] > 0) {
				if ($r = $this->q('
						SELECT 	`fs_botschafter`.`bezirk_id`,
								`fs_bezirk`.`has_children`,
								`fs_bezirk`.`parent_id`,
								`fs_bezirk`.name,
								`fs_bezirk`.id,
								`fs_bezirk`.type

						FROM 	`fs_botschafter`,
								`fs_bezirk`

						WHERE 	`fs_bezirk`.`id` = `fs_botschafter`.`bezirk_id`

						AND 	`fs_botschafter`.`foodsaver_id` = ' . $this->intval($fs['id']) . '
				')
				) {
					$_SESSION['client']['botschafter'] = $r;
					$_SESSION['client']['group']['botschafter'] = true;
					$mailbox = true;
					foreach ($r as $rr) {
						if (!$this->q('SELECT foodsaver_id FROM `fs_foodsaver_has_bezirk` WHERE foodsaver_id = ' . $this->intval($fs['id']) . ' AND bezirk_id = ' . (int)$rr['id'] . ' AND active = 1')) {
							$this->insert('
							REPLACE INTO `fs_foodsaver_has_bezirk`
							(
								`bezirk_id`,
								`foodsaver_id`,
								`active`,
								`added`
							)
							VALUES
							(
								' . (int)$rr['id'] . ',
								' . (int)$fs['id'] . ',
								1,
								NOW()
							)
						');
						}
					}
				}

				if ($r = $this->q('
							SELECT 	b.`id`,
									b.name,
									b.type,
									b.`master`

							FROM 	`fs_foodsaver_has_bezirk` hb,
									`fs_bezirk` b

							WHERE 	hb.bezirk_id = b.id
							AND 	`foodsaver_id` = ' . $this->intval($fs['id']) . '
							AND 	hb.active = 1

							ORDER BY b.name
					')
				) {
					$_SESSION['client']['bezirke'] = array();
					foreach ($r as $rr) {
						$_SESSION['client']['bezirke'][$rr['id']] = array(
							'id' => $rr['id'],
							'name' => $rr['name'],
							'type' => $rr['type']
						);
					}
				}
			}
			$_SESSION['client']['betriebe'] = false;
			if ($r = $this->q('
						SELECT 	b.`id`,
								b.name

						FROM 	`fs_betrieb_team` bt,
								`fs_betrieb` b

						WHERE 	bt.betrieb_id = b.id
						AND 	bt.`foodsaver_id` = ' . $this->intval($fs['id']) . '
						AND 	bt.active = 1
						ORDER BY b.name
				')
			) {
				$_SESSION['client']['betriebe'] = array();
				foreach ($r as $rr) {
					$_SESSION['client']['betriebe'][$rr['id']] = $rr;
				}
			}

			if ($r = $this->q('
						SELECT 	`betrieb_id`

						FROM 	`fs_betrieb_team`

						WHERE 	`foodsaver_id` = ' . $this->intval($fs['id']) . '
						AND 	`verantwortlich` = 1
			')
			) {
				$_SESSION['client']['verantwortlich'] = $r;
				$_SESSION['client']['group']['verantwortlich'] = true;
				$mailbox = true;
			}
			S::set('mailbox', $mailbox);
		} else {
			$this->func->goPage('logout');
		}
	}

	public function getTables()
	{
		$out = $this->q('SHOW TABLES');

		$tables = array();
		foreach ($out as $t) {
			$tables[] = end($t);
		}

		$out = array();
		foreach ($tables as $key => $t) {
			$out[$t] = $this->q('SHOW FULL COLUMNS FROM `' . $t . '`');
		}

		return $out;
	}

	public function getValues($fields, $table, $id)
	{
		$fields = implode('`,`', $fields);

		return $this->qRow('
			SELECT 	`' . $fields . '`
			FROM 	`fs_' . $table . '`
			WHERE 	`id` = ' . $this->intval($id) . '
		');
	}

	public function getVal($field, $table, $id)
	{
		if (!isset($this->values[$field . '-' . $table . '-' . $id])) {
			$this->values[$field . '-' . $table . '-' . $id] = $this->qOne('
			SELECT 	`' . $field . '`
			FROM 	`fs_' . $table . '`
			WHERE 	`id` = ' . $this->intval($id) . '
		');
		}

		return $this->values[$field . '-' . $table . '-' . $id];
	}

	public function updateFields($fields, $table, $id)
	{
		$sql = array();
		foreach ($fields as $k => $f) {
			if (preg_replace('/[^0-9]/', '', $f) == $f) {
				$sql[] = '`' . $k . '`=' . $this->intval($f);
			} else {
				$sql[] = '`' . $k . '`=' . $this->strval($f);
			}
		}

		return $this->update('UPDATE `' . $table . '` SET ' . implode(',', $sql) . ' WHERE `id` = ' . (int)$id);
	}

	public function getTable($fields, $table, $where = '')
	{
		return $this->q('
			SELECT 	`' . implode('`,`', $fields) . '`
			FROM 	`fs_' . $table . '`
			' . $where . '
		');
	}

	/**
	 * set option is an key value store each var is avalable in the user session.
	 *
	 * @param string $key
	 * @param $val
	 */
	public function setOption($key, $val)
	{
		$options = array();
		if ($opt = $this->getVal('option', 'foodsaver', $this->func->fsId())) {
			$options = unserialize($opt);
		}

		$options[$key] = $val;

		return $this->update('UPDATE fs_foodsaver SET `option` = ' . $this->strval(serialize($options)) . ' WHERE id = ' . (int)$this->func->fsId());
	}
}
