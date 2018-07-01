<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Region\RegionGateway;

class StoreGateway extends BaseGateway
{
	private $regionGateway;

	public function __construct(Database $db, RegionGateway $regionGateway)
	{
		parent::__construct($db);

		$this->regionGateway = $regionGateway;
	}

	public function getBetrieb($id)
	{
		$out = $this->db->fetch('
		SELECT		`id`,
					plz,
					`fs_betrieb`.bezirk_id,
					`fs_betrieb`.kette_id,
					`fs_betrieb`.betrieb_kategorie_id,
					`fs_betrieb`.name,
					`fs_betrieb`.str,
					`fs_betrieb`.hsnr,
					`fs_betrieb`.stadt,
					`fs_betrieb`.lat,
					`fs_betrieb`.lon,
					CONCAT(`fs_betrieb`.str, " ",`fs_betrieb`.hsnr) AS anschrift,
					`fs_betrieb`.`betrieb_status_id`,
					`fs_betrieb`.status_date,
					`fs_betrieb`.ansprechpartner,
					`fs_betrieb`.telefon,
					`fs_betrieb`.email,
					`fs_betrieb`.fax,
					`kette_id`

		FROM 		`fs_betrieb`

		WHERE 		`fs_betrieb`.`id` = :id',
		[':id' => $id]);

		$out['verantwortlicher'] = '';
		if ($bezirk = $this->regionGateway->getBezirkName($out['bezirk_id'])) {
			$out['bezirk'] = $bezirk;
		}
		if ($verantwortlich = $this->getBiebsForStore($id)) {
			$out['verantwortlicher'] = $verantwortlich;
		}
		if ($kette = $this->getOne_kette($out['kette_id'])) {
			$out['kette'] = $kette;
		}

		$out['notizen'] = $this->getBetriebNotiz($id);

		return $out;
	}

	public function getMapsBetriebe($bezirk_id)
	{
		return $this->db->fetchAll('
			SELECT 	fs_betrieb.id,
					`fs_betrieb`.betrieb_status_id,
					fs_betrieb.plz,
					`lat`,
					`lon`,
					`stadt`,
					fs_betrieb.kette_id,
					fs_betrieb.betrieb_kategorie_id,
					fs_betrieb.name,
					CONCAT(fs_betrieb.str," ",fs_betrieb.hsnr) AS anschrift,
					fs_betrieb.str,
					fs_betrieb.hsnr,
					fs_betrieb.`betrieb_status_id`

			FROM 	fs_betrieb

			WHERE 	fs_betrieb.bezirk_id = :bezirk_id

			AND `lat` != ""',
			[':bezirk_id' => $bezirk_id]
		);
	}

	public function getMyBetriebe($fs_id, $bezirk_id, $options = array())
	{
		// TODO this need proper testing!!!
		$betriebe = $this->db->fetchAll('
			SELECT 	fs_betrieb.id,
						`fs_betrieb`.betrieb_status_id,
						fs_betrieb.plz,
						fs_betrieb.kette_id,

						fs_betrieb.ansprechpartner,
						fs_betrieb.fax,
						fs_betrieb.telefon,
						fs_betrieb.email,

						fs_betrieb.betrieb_kategorie_id,
						fs_betrieb.name,
						CONCAT(fs_betrieb.str," ",fs_betrieb.hsnr) AS anschrift,
						fs_betrieb.str,
						fs_betrieb.hsnr,
						fs_betrieb.`betrieb_status_id`,
						fs_betrieb_team.verantwortlich,
						fs_betrieb_team.active

				FROM 	fs_betrieb,
						fs_betrieb_team

				WHERE 	fs_betrieb.id = fs_betrieb_team.betrieb_id

				AND 	fs_betrieb_team.foodsaver_id = :fs_id

				ORDER BY fs_betrieb_team.verantwortlich DESC, fs_betrieb.name ASC
		', [':fs_id' => $fs_id]);

		$out = array();
		$out['verantwortlich'] = array();
		$out['team'] = array();
		$out['waitspringer'] = array();
		$out['anfrage'] = array();

		$already_in = array();

		if (is_array($betriebe)) {
			foreach ($betriebe as $b) {
				$already_in[$b['id']] = true;
				if ($b['verantwortlich'] == 0) {
					if ($b['active'] == 0) {
						$out['anfrage'][] = $b;
					} elseif ($b['active'] == 1) {
						$out['team'][] = $b;
					} elseif ($b['active'] == 2) {
						$out['waitspringer'][] = $b;
					}
				} else {
					$out['verantwortlich'][] = $b;
				}
			}
		}
		unset($betriebe);

		if (!isset($options['sonstige'])) {
			$options['sonstige'] = true;
		}

		if ($options['sonstige']) {
			$child_region_ids = $this->regionGateway->listIdsForDescendantsAndSelf($bezirk_id);
			$placeholders = $this->db->generatePlaceholders(count($child_region_ids));

			$out['sonstige'] = array();
			$betriebe = $this->db->fetchAll(
		'SELECT 		b.id,
						b.betrieb_status_id,
						b.plz,
						b.kette_id,

						b.ansprechpartner,
						b.fax,
						b.telefon,
						b.email,

						b.betrieb_kategorie_id,
						b.name,
						CONCAT(b.str," ",b.hsnr) AS anschrift,
						b.str,
						b.hsnr,
						b.`betrieb_status_id`,
						bz.name AS bezirk_name

				FROM 	fs_betrieb b,
						fs_bezirk bz

				WHERE 	b.bezirk_id = bz.id
				AND 	bezirk_id IN(' . $placeholders . ')
				ORDER BY bz.name DESC',
			$child_region_ids);

			foreach ($betriebe as $b) {
				if (!isset($already_in[$b['id']])) {
					$out['sonstige'][] = $b;
				}
			}
		}

		return $out;
	}

	public function getMyBetrieb($fs_id, $id)
	{
		$out = $this->db->fetch('
			SELECT
			b.`id`,
			b.`betrieb_status_id`,
			b.`bezirk_id`,
			b.`plz`,
			b.`stadt`,
			b.`lat`,
			b.`lon`,
			b.`kette_id`,
			b.`betrieb_kategorie_id`,
			b.`name`,
			b.`str`,
			b.`hsnr`,
			b.`status_date`,
			b.`status`,
			b.`ansprechpartner`,
			b.`telefon`,
			b.`fax`,
			b.`email`,
			b.`begin`,
			b.`besonderheiten`,
			b.`public_info`,
			b.`public_time`,
			b.`ueberzeugungsarbeit`,
			b.`presse`,
			b.`sticker`,
			b.`abholmenge`,
			b.`team_status`,
			b.`prefetchtime`,
			b.`team_conversation_id`,
			b.`springer_conversation_id`,
			count(DISTINCT(a.date)) AS pickup_count

			FROM 		`fs_betrieb` b
			LEFT JOIN   `fs_abholer` a
			ON a.betrieb_id = b.id

			WHERE 		b.`id` = :id
			GROUP BY b.`id`',
			[':id' => $id]
		);
		if (!$out) {
			return $out;
		}

		$out['lebensmittel'] = $this->db->fetch('
				SELECT 		l.`id`,
							l.name
				FROM 		`fs_betrieb_has_lebensmittel` hl,
							`fs_lebensmittel` l
				WHERE 		l.id = hl.lebensmittel_id
				AND 		`betrieb_id` = :id
		', [':id' => $id]);

		$out['foodsaver'] = $this->getBetriebTeam($id);

		$out['springer'] = $this->getBetriebSpringer($id);

		$out['requests'] = $this->db->fetch('
				SELECT 		fs.`id`,
							fs.photo,
							CONCAT(fs.name," ",fs.nachname) AS name,
							name as vorname,
							fs.sleep_status

				FROM 		`fs_betrieb_team` t,
							`fs_foodsaver` fs

				WHERE 		fs.id = t.foodsaver_id
				AND 		`betrieb_id` = :id
				AND 		t.active = 0
				AND			fs.deleted_at IS NULL
		', [':id' => $id]);

		$out['verantwortlich'] = false;
		$foodsaver = array();
		$out['team_js'] = array();
		$out['team'] = array();
		$out['jumper'] = false;

		if (!empty($out['springer'])) {
			foreach ($out['springer'] as $v) {
				if ($v['id'] == $fs_id) {
					$out['jumper'] = true;
				}
			}
		}

		if (!empty($out['foodsaver'])) {
			$out['team'] = array();
			foreach ($out['foodsaver'] as $v) {
				$out['team_js'][] = $v['id'];
				$foodsaver[$v['id']] = $v['name'];
				$out['team'][] = array('id' => $v['id'], 'value' => $v['name']);
				if ($v['verantwortlich'] == 1) {
					$out['verantwortlicher'] = $v['id'];
					if ($v['id'] == $fs_id) {
						$out['verantwortlich'] = true;
					}
				}
			}
		} else {
			$out['foodsaver'] = array();
		}
		$out['team_js'] = implode(',', $out['team_js']);

		$out['abholer'] = false;
		if ($abholer = $this->db->fetch('SELECT `betrieb_id`,`dow` FROM `fs_abholzeiten` WHERE `betrieb_id` = :id', [':id' => $id])) {
			$out['abholer'] = array();
			foreach ($abholer as $a) {
				if (!isset($out['abholer'][$a['dow']])) {
					$out['abholer'][$a['dow']] = array();
				}
			}
		}

		return $out;
	}

	public function getBetriebTeam($id)
	{
		return $this->db->fetchAll('
				SELECT 		fs.`id`,
							fs.`verified`,
							fs.`active`,
							fs.`telefon`,
							fs.`handy`,
							fs.photo,
							fs.quiz_rolle,
							fs.rolle,
							CONCAT(fs.name," ",fs.nachname) AS name,
							name as vorname,
							t.`verantwortlich`,
							t.`stat_last_update`,
							t.`stat_fetchcount`,
							t.`stat_first_fetch`,
							UNIX_TIMESTAMP(t.`stat_last_fetch`) AS last_fetch,
							UNIX_TIMESTAMP(t.`stat_add_date`) AS add_date,
							fs.sleep_status


				FROM 		`fs_betrieb_team` t,
							`fs_foodsaver` fs

				WHERE 		fs.id = t.foodsaver_id
				AND 		`betrieb_id` = :id
				AND 		t.active  = 1
				AND			fs.deleted_at IS NULL
				ORDER BY 	t.`stat_fetchcount` DESC
		', [':id' => $id]);
	}

	public function getBetriebSpringer($id)
	{
		return $this->db->fetch('
				SELECT 		fs.`id`,
							fs.`active`,
							fs.`telefon`,
							fs.`handy`,
							fs.photo,
							fs.rolle,
							CONCAT(fs.name," ",fs.nachname) AS name,
							name as vorname,
							t.`verantwortlich`,
							t.`stat_last_update`,
							t.`stat_fetchcount`,
							t.`stat_first_fetch`,
							UNIX_TIMESTAMP(t.`stat_add_date`) AS add_date,
							fs.sleep_status

				FROM 		`fs_betrieb_team` t,
							`fs_foodsaver` fs

				WHERE 		fs.id = t.foodsaver_id
				AND 		`betrieb_id` = :id
				AND 		t.active  = 2
				AND			fs.deleted_at IS NULL
		', [':id' => $id]);
	}

	public function getBiebsForStore($betrieb_id)
	{
		return $this->db->fetchValue(
			'
			SELECT 	`foodsaver_id` as id
			FROM fs_betrieb_team
			WHERE `betrieb_id` = :betrieb_id
			AND verantwortlich = 1
			AND `active` = 1',
			[':betrieb_id' => $betrieb_id]);
	}

	public function getAllFilialverantwortlich()
	{
		$verant = $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`email`

			FROM 	`fs_foodsaver` fs,
					`fs_betrieb_team` bt

			WHERE 	bt.foodsaver_id = fs.id

			AND 	bt.verantwortlich = 1
			AND		fs.deleted_at IS NULL
		');

		$out = array();
		foreach ($verant as $v) {
			$out[$v['id']] = $v;
		}

		return $out;
	}

	public function getEmailBiepBez($region_ids)
	{
		// TODO can probably be removed
		$placeholders = $this->db->generatePlaceholders(count($region_ids));

		$verant = $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`email`

			FROM 	`fs_foodsaver` fs,
					`fs_betrieb_team` bt,
					`fs_foodsaver_has_bezirk` b

			WHERE 	bt.foodsaver_id = fs.id
			AND 	bt.foodsaver_id = b.foodsaver_id
			AND 	bt.verantwortlich = 1
			AND		b.`bezirk_id` IN(' . $placeholders . ')
			AND		fs.deleted_at IS NULL
		', $region_ids);

		$out = array();
		foreach ($verant as $v) {
			$out[$v['id']] = $v;
		}

		return $out;
	}

	public function isVerantwortlich($fs_id, $betrieb_id)
	{
		return $this->db->fetchValue('
				SELECT 	betrieb_id
				FROM 	fs_betrieb_team
				WHERE 	betrieb_id = :bid
				AND 	foodsaver_id = :fs_id
				AND 	verantwortlich = 1
				AND 	active = 1
		', [':bid' => $betrieb_id, ':fs_id' => $fs_id]);
	}

	public function hasAnfrageAtStore($fs_id, $betrieb_id)
	{
		return $this->db->fetchValue('

				SELECT 	betrieb_id

				FROM 	fs_betrieb_team

				WHERE 	betrieb_id = :bid
				AND 	foodsaver_id = :fs_id
				AND 	verantwortlich = 0
				AND 	active = 0
		', [':bid' => $betrieb_id, ':fs_id' => $fs_id]);
	}

	public function addFetcher($fsid, $bid, $date, $confirm = 0)
	{
		return $this->db->insertIgnore('fs_abholer', [
			'foodsaver_id' => $fsid,
			'betrieb_id' => $bid,
			'date' => $date,
			'confirmed' => $confirm
		]);
	}

	public function addAbholer($betrieb_id, $foodsaver_id, $dow)
	{
		return $this->db->insert('fs_abholer', [
			'betrieb_id' => $betrieb_id,
			'foodsaver_id' => $foodsaver_id,
			'dow' => $dow
		]);
	}

	public function clearAbholer($betrieb_id)
	{
		return $this->db->delete('fs_abholer', ['betrieb_id' => $betrieb_id]);
	}

	public function confirmFetcher($fsid, $bid, $date)
	{
		return $this->db->update(
		'fs_abholer',
			['confirmed' => 1],
			['foodsaver_id' => $fsid, 'betrieb_id' => $bid, 'date' => $date]
		);
	}

	public function getAbholdates($bid, $dates)
	{
		// TODO needs testing
		if (!empty($dates)) {
			$dsql = array();
			foreach ($dates as $date => $time) {
				$dsql[] = $date;
			}
			$placeholders = $this->db->generatePlaceholders(count($dsql));

			$res = $this->db->fetchAll('
				SELECT 	fs.id,
						fs.name,
						fs.photo,
						a.date,
						a.confirmed
	
				FROM 	`fs_abholer` a,
						`fs_foodsaver` fs
	
				WHERE 	a.foodsaver_id = fs.id
				AND 	a.betrieb_id = ?
				AND  	a.date IN(' . $placeholders . ')
				AND		fs.deleted_at IS NULL',
				array_merge([$bid], $dsql)
			);
			global $g_data;
			foreach ($res as $r) {
				$key = 'fetch-' . str_replace(array(':', ' ', '-'), '', $r['date']);
				if (!isset($g_data[$key])) {
					$g_data[$key] = array();
				}
				$g_data[$key][] = $r;
			}

			return $res;
		}

		return false;
	}

	public function getAbholzeiten($betrieb_id)
	{
		if ($res = $this->db->fetchAll('SELECT `time`,`dow`,`fetcher` FROM `fs_abholzeiten` WHERE `betrieb_id` = :id', [':id' => $betrieb_id])) {
			$out = array();
			foreach ($res as $r) {
				$out[$r['dow'] . '-' . $r['time']] = array(
					'dow' => $r['dow'],
					'time' => $r['time'],
					'fetcher' => $r['fetcher']
				);
			}

			ksort($out);

			return $out;
		}

		return false;
	}

	public function getBetriebConversation($bid, $springerConversation = false)
	{
		if ($springerConversation) {
			$ccol = 'springer_conversation_id';
		} else {
			$ccol = 'team_conversation_id';
		}

		return $this->db->fetchValue('SELECT ' . $ccol . ' FROM `fs_betrieb` WHERE `id` = :id', [':id' => $bid]);
	}

	public function changeBetriebStatus($fs_id, $bid, $status)
	{
		$last = $this->db->fetch('SELECT id, milestone FROM `fs_betrieb_notiz` WHERE `betrieb_id` = :id ORDER BY id DESC LIMIT 1', [':id' => $bid]);

		if ($last['milestone'] == 3) {
			$this->db->delete('fs_betrieb_notiz', ['id' => $last['id']]);
		}

		$this->add_betrieb_notiz(array(
			'foodsaver_id' => $fs_id,
			'betrieb_id' => $bid,
			'text' => 'status_msg_' . (int)$status,
			'zeit' => date('Y-m-d H:i:s'),
			'milestone' => 3
		));

		return $this->db->update(
			'fs_betrieb',
			['betrieb_status_id' => $status],
			['id' => $bid]
		);
	}

	public function add_betrieb_notiz($data)
	{
		$last = 0;
		if (isset($data['last']) && $data['last'] == 1) {
			$this->db->update(
				'fs_betrieb_notiz',
				['last' => 0],
				['betrieb_id' => $data['betrieb_id'], 'last' => 1]
			);
			$last = 1;
		}

		return $this->db->insert('fs_betrieb_notiz', [
			'foodsaver_id' => $data['foodsaver_id'],
			'betrieb_id' => $data['betrieb_id'],
			'milestone' => $data['milestone'],
			'text' => strip_tags($data['text']),
			'zeit' => $data['zeit'],
			'last' => $last
		]);
	}

	public function deleteBPost($id)
	{
		return $this->db->delete('fs_betrieb_notiz', ['id' => $id]);
	}

	public function getTeamleader($betrieb_id)
	{
		return $this->db->fetch(
		'SELECT 	fs.`id`,CONCAT(fs.name," ",nachname) AS name  
				FROM fs_betrieb_team t, fs_foodsaver fs
				WHERE t.foodsaver_id = fs.id
				AND `betrieb_id` = :id
				AND t.verantwortlich = 1
				AND fs.`active` = 1
				AND	fs.deleted_at IS NULL',
			[':id' => $betrieb_id]);
	}

	public function isInTeam($fs_id, $bid)
	{
		// TODO needs testing
		if ($this->db->fetchValue(
			   'SELECT `foodsaver_id` 
				FROM `fs_betrieb_team` 
				WHERE foodsaver_id = :fs_id
				AND betrieb_id = :bid
				AND active IN(1,2)',
			[':fs_id' => $fs_id, ':bid' => $bid])
		) {
			return true;
		}

		return false;
	}

	/* retrieves all biebs that are biebs for a given bezirk (by being bieb in a Betrieb that is part of that bezirk, which is semantically not the same we use on platform) */
	public function getBiebIds($bezirk)
	{
		return $this->db->fetchAllValues('SELECT DISTINCT bt.foodsaver_id FROM `fs_bezirk_closure` c
			INNER JOIN `fs_betrieb` b ON c.bezirk_id = b.bezirk_id
			INNER JOIN `fs_betrieb_team` bt ON bt.betrieb_id = b.id
			INNER JOIN `fs_foodsaver` fs ON fs.id = bt.foodsaver_id
			WHERE c.ancestor_id = :id AND bt.verantwortlich = 1 AND fs.deleted_at IS NULL',
			[':id' => $bezirk]);
	}

	/*
	 * Private methods
	 */

	private function getOne_kette($id)
	{
		return $this->db->fetch('
			SELECT
			`id`,
			`name`,
			`logo`

			FROM 		`fs_kette`

			WHERE 		`id` = :id',
			[':id' => $id]);
	}

	private function getBetriebNotiz($id)
	{
		return $this->db->fetchAll('
			SELECT
			`id`,
			`foodsaver_id`,
			`betrieb_id`,
			`text`,
			`zeit`,
			UNIX_TIMESTAMP(`zeit`) AS zeit_ts

			FROM 		`fs_betrieb_notiz`

			WHERE `betrieb_id` = :id',
		[':id' => $id]);
	}
}
