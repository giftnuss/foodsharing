<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Modules\Core\Model;
use Foodsharing\Modules\Message\MessageModel;

class StoreModel extends Model
{
	public function addFetchDate($bid, $time, $fetchercount)
	{
		return $this->insert('
			INSERT INTO `' . PREFIX . 'fetchdate`
			(
				`betrieb_id`, 
				`time`, 
				`fetchercount`
			) 
			VALUES 
			(
				' . (int)$bid . ',
				' . $this->dateval($time) . ',
				' . (int)$fetchercount . '
			)
		');
	}

	public function updateBetriebBezirk($betrieb_id, $bezirk_id)
	{
		return $this->update('UPDATE ' . PREFIX . 'betrieb SET bezirk_id = ' . (int)$bezirk_id . ' WHERE id = ' . (int)$betrieb_id);
	}

	public function getFetchHistory($betrieb_id, $from, $to)
	{
		return $this->q('
			SELECT
				fs.id,
				fs.name,
				fs.nachname,
				fs.photo,
				a.date,
				UNIX_TIMESTAMP(a.date) AS date_ts
	
			FROM
				' . PREFIX . 'foodsaver fs,
				' . PREFIX . 'abholer a
	
			WHERE
				a.foodsaver_id = fs.id
	
			AND
				a.betrieb_id = ' . (int)$betrieb_id . '
	
			AND
				a.date >= ' . $this->dateVal($from) . '
	
			AND
				a.date <= ' . $this->dateVal($to) . '
	
			ORDER BY
				a.date
	
		');
	}

	public function deldate($bid, $date)
	{
		$this->del('DELETE FROM `' . PREFIX . 'abholer` WHERE `betrieb_id` = ' . (int)$bid . ' AND `date` = ' . $this->dateval($date));

		return $this->del('DELETE FROM `' . PREFIX . 'fetchdate` WHERE `betrieb_id` = ' . (int)$bid . ' AND `time` = ' . $this->dateval($date));
	}

	public function listMyBetriebe()
	{
		return $this->q('
			SELECT 	b.id,
					b.name,
					b.plz,
					b.stadt,
					b.str,
					b.hsnr

			FROM
				' . PREFIX . 'betrieb b,
				' . PREFIX . 'betrieb_team t
				
			WHERE
				b.id = t.betrieb_id
				
			AND
				t.foodsaver_id = ' . $this->func->fsId() . '
				
			AND
				t.active = 1
		');
	}

	public function listUpcommingFetchDates($bid)
	{
		if ($dates = $this->q('
			SELECT 	`time`,
					UNIX_TIMESTAMP(`time`) AS `time_ts`,
					`fetchercount`
			FROM 	' . PREFIX . 'fetchdate
			WHERE 	`betrieb_id` = ' . (int)$bid . '
			AND 	`time` > NOW()
		')
		) {
			$out = array();
			foreach ($dates as $d) {
				$out[date('Y-m-d H-i', $d['time_ts'])] = array(
					'time' => date('H:i:s', $d['time_ts']),
					'fetcher' => $d['fetchercount'],
					'additional' => true,
					'datetime' => $d['time']
				);
			}

			return $out;
		}

		return false;
	}

	/* delete fetch dates a user signed up for.
	 * Either a specific fetch date (fsid, bid and date set)
	 * or all fetch dates for a store (only fsid, bid set)
	 * or all fetch dates for a user (only fsid set)
	 */
	public function deleteFetchDate($fsid, $bid = null, $date = null)
	{
		if ($date !== null && $bid !== null) {
			return $this->del('DELETE FROM `' . PREFIX . 'abholer` WHERE `betrieb_id` = ' . (int)$bid . ' AND `foodsaver_id` = ' . (int)$fsid . ' AND `date` = ' . $this->dateval($date));
		} elseif ($bid !== null) {
			return $this->del('DELETE FROM `' . PREFIX . 'abholer` WHERE `betrieb_id` = ' . (int)$bid . ' AND `foodsaver_id` = ' . (int)$fsid . ' AND `date` = > now()');
		} else {
			return $this->del('DELETE FROM `' . PREFIX . 'abholer` WHERE `foodsaver_id` = ' . (int)$fsid . ' AND `date` > now()');
		}
	}

	public function signout($bid, $fsid)
	{
		$bid = $this->intval($bid);
		$fsid = $this->intval($fsid);
		$this->del('DELETE FROM `' . PREFIX . 'betrieb_team` WHERE `betrieb_id` = ' . $bid . ' AND `foodsaver_id` = ' . $fsid . ' ');
		$this->del('DELETE FROM `' . PREFIX . 'abholer` WHERE `betrieb_id` = ' . $bid . ' AND `foodsaver_id` = ' . $fsid . ' AND `date` > NOW()');

		$msg = new MessageModel();

		if ($tcid = $msg->getBetriebConversation($bid)) {
			$msg->deleteUserFromConversation($tcid, $fsid, true);
		}
		if ($scid = $msg->getBetriebConversation($bid, true)) {
			$msg->deleteUserFromConversation($scid, $fsid, true);
		}
	}

	public function getBetriebBezirkID($id)
	{
		$out = $this->qRow('
			SELECT
			`bezirk_id`

			FROM 		`' . PREFIX . 'betrieb`

			WHERE 		`id` = ' . $this->intval($id));

		return $out;
	}

	public function get_betrieb_kategorie()
	{
		$out = $this->q('
				SELECT
				`id`,
				`name`
				
				FROM 		`' . PREFIX . 'betrieb_kategorie`
				ORDER BY `name`');

		return $out;
	}

	public function get_betrieb_status()
	{
		$out = $this->q('
				SELECT
				`id`,
				`name`
				
				FROM 		`' . PREFIX . 'betrieb_status`
				ORDER BY `name`');

		return $out;
	}

	public function getOne_betrieb($id)
	{
		$out = $this->qRow('
			SELECT
			`id`,
			`betrieb_status_id`,
			`bezirk_id`,
			`plz`,
			`stadt`,
			`lat`,
			`lon`,
			`kette_id`,
			`betrieb_kategorie_id`,
			`name`,
			`str`,
			`hsnr`,
			`status_date`,
			`status`,
			`ansprechpartner`,
			`telefon`,
			`fax`,
			`email`,
			`begin`,
			`besonderheiten`,
			`ueberzeugungsarbeit`,
			`presse`,
			`sticker`,
			`abholmenge`,
			`prefetchtime`,
			`public_info`,
			`public_time`

			FROM 		`' . PREFIX . 'betrieb`

			WHERE 		`id` = ' . $this->intval($id));

		$out['lebensmittel'] = $this->qCol('
				SELECT 		`lebensmittel_id`

				FROM 		`' . PREFIX . 'betrieb_has_lebensmittel`
				WHERE 		`betrieb_id` = ' . $this->intval($id) . '
			');
		$out['foodsaver'] = $this->qCol('
				SELECT 		`foodsaver_id`

				FROM 		`' . PREFIX . 'betrieb_team`
				WHERE 		`betrieb_id` = ' . $this->intval($id) . '
				AND 		`active` = 1
			');

		return $out;
	}

	public function getBetriebLeader($bid)
	{
		return $this->qCol('
				SELECT 		t.`foodsaver_id`,
							t.`verantwortlich`

				FROM 		`' . PREFIX . 'betrieb_team` t
				INNER JOIN  `' . PREFIX . 'foodsaver` fs ON fs.id = t.foodsaver_id

				WHERE 		t.`betrieb_id` = ' . $this->intval($bid) . '
				AND 		t.active = 1
				AND 		t.verantwortlich = 1
				AND			fs.deleted_at IS NULL
		');
	}

	public function getBasics_lebensmittel()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`

			FROM 		`' . PREFIX . 'lebensmittel`
			ORDER BY `name`');
	}

	public function getBasics_kette()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`

			FROM 		`' . PREFIX . 'kette`
			ORDER BY `name`');
	}

	public function listBetriebReq($bezirk_id)
	{
		return $this->q('
				SELECT 	' . PREFIX . 'betrieb.id,
						`' . PREFIX . 'betrieb`.betrieb_status_id,
						' . PREFIX . 'betrieb.plz,
						' . PREFIX . 'betrieb.added,
						`stadt`,
						' . PREFIX . 'betrieb.kette_id,
						' . PREFIX . 'betrieb.betrieb_kategorie_id,
						' . PREFIX . 'betrieb.name,
						CONCAT(' . PREFIX . 'betrieb.str," ",' . PREFIX . 'betrieb.hsnr) AS anschrift,
						' . PREFIX . 'betrieb.str,
						' . PREFIX . 'betrieb.hsnr,
						' . PREFIX . 'betrieb.`betrieb_status_id`,
						' . PREFIX . 'bezirk.name AS bezirk_name

				FROM 	' . PREFIX . 'betrieb,
						' . PREFIX . 'bezirk

				WHERE 	' . PREFIX . 'betrieb.bezirk_id = ' . PREFIX . 'bezirk.id
				AND 	' . PREFIX . 'betrieb.bezirk_id IN(' . implode(',', $this->getChildBezirke($bezirk_id)) . ')


		');
	}

	public function update_betrieb($id, $data)
	{
		if (isset($data['lebensmittel']) && is_array($data['lebensmittel'])) {
			$this->del('
					DELETE FROM 	`fs_betrieb_has_lebensmittel`
					WHERE 			`betrieb_id` = ' . $this->intval($id) . '
				');

			foreach ($data['lebensmittel'] as $lebensmittel_id) {
				$this->insert('
						INSERT INTO `' . PREFIX . 'betrieb_has_lebensmittel`
						(
							`betrieb_id`,
							`lebensmittel_id`
						)
						VALUES
						(
							' . $this->intval($id) . ',
							' . $this->intval($lebensmittel_id) . '
						)
					');
			}
		}

		if (!isset($data['status_date'])) {
			$data['status_date'] = date('Y-m-d H:i:s');
		}

		return $this->update('
		UPDATE 	`' . PREFIX . 'betrieb`

		SET 	`betrieb_status_id` =  ' . $this->intval($data['betrieb_status_id']) . ',
				`bezirk_id` =  ' . $this->intval($data['bezirk_id']) . ',
				`plz` =  ' . $this->strval($data['plz']) . ',
				`stadt` =  ' . $this->strval($data['stadt']) . ',
				`lat` =  ' . $this->strval($data['lat']) . ',
				`lon` =  ' . $this->strval($data['lon']) . ',
				`kette_id` =  ' . $this->intval($data['kette_id']) . ',
				`betrieb_kategorie_id` =  ' . $this->intval($data['betrieb_kategorie_id']) . ',
				`name` =  ' . $this->strval($data['name']) . ',
				`str` =  ' . $this->strval($data['str']) . ',
				`hsnr` =  ' . $this->strval($data['hsnr']) . ',
				`status_date` =  ' . $this->dateval($data['status_date']) . ',
				`ansprechpartner` =  ' . $this->strval($data['ansprechpartner']) . ',
				`telefon` =  ' . $this->strval($data['telefon']) . ',
				`fax` =  ' . $this->strval($data['fax']) . ',
				`email` =  ' . $this->strval($data['email']) . ',
				`begin` =  ' . $this->dateval($data['begin']) . ',
				`besonderheiten` =  ' . $this->strval($data['besonderheiten']) . ',
				`public_info` =  ' . $this->strval($data['public_info']) . ',
				`public_time` =  ' . $this->intval($data['public_time']) . ',
				`ueberzeugungsarbeit` =  ' . $this->intval($data['ueberzeugungsarbeit']) . ',
				`presse` =  ' . $this->intval($data['presse']) . ',
				`sticker` =  ' . $this->intval($data['sticker']) . ',
				`abholmenge` =  ' . $this->intval($data['abholmenge']) . ',
				`prefetchtime` = ' . (int)$data['prefetchtime'] . '

		WHERE 	`id` = ' . $this->intval($id));
	}

	public function add_betrieb($data)
	{
		$id = $this->insert('
			INSERT INTO 	`' . PREFIX . 'betrieb`
			(
			`betrieb_status_id`,
			`bezirk_id`,
			`added`,
			`plz`,
			`stadt`,
			`lat`,
			`lon`,
			`kette_id`,
			`betrieb_kategorie_id`,
			`name`,
			`str`,
			`hsnr`,
			`status_date`,
			`status`,
			`ansprechpartner`,
			`telefon`,
			`fax`,
			`email`,
			`begin`,
			`besonderheiten`,
			`public_info`,
			`public_time`,
			`ueberzeugungsarbeit`,
			`presse`,
			`sticker`,
      `abholmenge`
			)
			VALUES
			(
			' . $this->intval($data['betrieb_status_id']) . ',
			' . $this->intval($data['bezirk_id']) . ',
			NOW(),
			' . $this->strval($data['plz']) . ',
			' . $this->strval($data['stadt']) . ',
			' . $this->strval($data['lat']) . ',
			' . $this->strval($data['lon']) . ',
			' . $this->intval($data['kette_id']) . ',
			' . $this->intval($data['betrieb_kategorie_id']) . ',
			' . $this->strval($data['name']) . ',
			' . $this->strval($data['str']) . ',
			' . $this->strval($data['hsnr']) . ',
			' . $this->dateval($data['status_date']) . ',
			' . $this->intval($data['betrieb_status_id']) . ',
			' . $this->strval($data['ansprechpartner']) . ',
			' . $this->strval($data['telefon']) . ',
			' . $this->strval($data['fax']) . ',
			' . $this->strval($data['email']) . ',
			' . $this->dateval($data['begin']) . ',
			' . $this->strval($data['besonderheiten']) . ',
			' . $this->strval($data['public_info']) . ',
			' . $this->intval($data['public_time']) . ',
			' . $this->intval($data['ueberzeugungsarbeit']) . ',
			' . $this->intval($data['presse']) . ',
			' . $this->intval($data['sticker']) . ',
      ' . $this->intval($data['abholmenge']) . '
			)');

		if (isset($data['lebensmittel']) && is_array($data['lebensmittel'])) {
			foreach ($data['lebensmittel'] as $lebensmittel_id) {
				$this->insert('
						INSERT INTO `' . PREFIX . 'betrieb_has_lebensmittel`
						(
							`betrieb_id`,
							`lebensmittel_id`
						)
						VALUES
						(
							' . $this->intval($id) . ',
							' . $this->intval($lebensmittel_id) . '
						)
					');
			}
		}

		if (isset($data['foodsaver']) && is_array($data['foodsaver'])) {
			foreach ($data['foodsaver'] as $foodsaver_id) {
				$this->insert('
						REPLACE INTO `' . PREFIX . 'betrieb_team`
						(
							`betrieb_id`,
							`foodsaver_id`,
							`verantwortlich`,
							`active`
						)
						VALUES
						(
							' . $this->intval($id) . ',
							' . $this->intval($foodsaver_id) . ',
							1,
							1
						)
					');
			}
		}

		$this->createTeamConversation($id);
		$this->createSpringerConversation($id);

		return $id;
	}
}
