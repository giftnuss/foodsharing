<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Helpers\TranslationHelper;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Modules\Message\MessageModel;
use Foodsharing\Modules\Region\RegionGateway;

class StoreModel extends Db
{
	private $messageModel;
	private $bellGateway;
	private $storeGateway;
	private $regionGateway;
	private $messagesGateway;
	private $translationHelper;

	public function __construct(
		MessageModel $messageModel,
		BellGateway $bellGateway,
		StoreGateway $storeGateway,
		RegionGateway $regionGateway,
		MessageGateway $messagesGateway,
		TranslationHelper $translationHelper
	) {
		$this->messageModel = $messageModel;
		$this->bellGateway = $bellGateway;
		$this->storeGateway = $storeGateway;
		$this->regionGateway = $regionGateway;
		$this->messagesGateway = $messagesGateway;
		$this->translationHelper = $translationHelper;

		parent::__construct();
	}

	public function updateBetriebBezirk($betrieb_id, $bezirk_id)
	{
		return $this->update('UPDATE fs_betrieb SET bezirk_id = ' . (int)$bezirk_id . ' WHERE id = ' . (int)$betrieb_id);
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
				fs_foodsaver fs,
				fs_abholer a
	
			WHERE
				a.foodsaver_id = fs.id
	
			AND
				a.betrieb_id = ' . (int)$betrieb_id . '
	
			AND
				a.date >= ' . $this->dateval($from) . '
	
			AND
				a.date <= ' . $this->dateval($to) . '
			
			AND
				a.confirmed = 1
	
			ORDER BY
				a.date
	
		');
	}

	public function deldate($storeId, $date)
	{
		$this->del('DELETE FROM `fs_abholer` WHERE `betrieb_id` = ' . (int)$storeId . ' AND `date` = ' . $this->dateval($date));

		return $this->del('DELETE FROM `fs_fetchdate` WHERE `betrieb_id` = ' . (int)$storeId . ' AND `time` = ' . $this->dateval($date));
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
				fs_betrieb b,
				fs_betrieb_team t
				
			WHERE
				b.id = t.betrieb_id
				
			AND
				t.foodsaver_id = ' . $this->session->id() . '
				
			AND
				t.active = 1
		');
	}

	public function listUpcommingFetchDates($storeId)
	{
		if ($dates = $this->q('
			SELECT 	`time`,
					UNIX_TIMESTAMP(`time`) AS `time_ts`,
					`fetchercount`
			FROM 	fs_fetchdate
			WHERE 	`betrieb_id` = ' . (int)$storeId . '
			AND 	`time` > NOW()
		')
		) {
			$out = [];
			foreach ($dates as $d) {
				$out[date('Y-m-d H-i', $d['time_ts'])] = [
					'time' => date('H:i:s', $d['time_ts']),
					'fetcher' => $d['fetchercount'],
					'additional' => true,
					'datetime' => $d['time']
				];
			}

			return $out;
		}

		return false;
	}

	public function signout($storeId, $fsId)
	{
		$storeId = (int)$storeId;
		$fsId = (int)$fsId;
		$this->del('DELETE FROM `fs_betrieb_team` WHERE `betrieb_id` = ' . $storeId . ' AND `foodsaver_id` = ' . $fsId . ' ');
		$this->del('DELETE FROM `fs_abholer` WHERE `betrieb_id` = ' . $storeId . ' AND `foodsaver_id` = ' . $fsId . ' AND `date` > NOW()');

		if ($tcid = $this->storeGateway->getBetriebConversation($storeId)) {
			$this->messageModel->deleteUserFromConversation($tcid, $fsId, true);
		}
		if ($scid = $this->storeGateway->getBetriebConversation($storeId, true)) {
			$this->messageModel->deleteUserFromConversation($scid, $fsId, true);
		}
	}

	public function getBetriebBezirkID($storeId)
	{
		$out = $this->qRow('
			SELECT
			`bezirk_id`

			FROM 		`fs_betrieb`

			WHERE 		`id` = ' . (int)$storeId);

		return $out;
	}

	public function get_betrieb_kategorie()
	{
		$out = $this->q('
				SELECT
				`id`,
				`name`
				
				FROM 		`fs_betrieb_kategorie`
				ORDER BY `name`');

		return $out;
	}

	public function getOne_betrieb($storeId)
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

			FROM 		`fs_betrieb`

			WHERE 		`id` = ' . (int)$storeId);

		$out['lebensmittel'] = $this->qCol('
				SELECT 		`lebensmittel_id`

				FROM 		`fs_betrieb_has_lebensmittel`
				WHERE 		`betrieb_id` = ' . (int)$storeId . '
			');
		$out['foodsaver'] = $this->qCol('
				SELECT 		`foodsaver_id`

				FROM 		`fs_betrieb_team`
				WHERE 		`betrieb_id` = ' . (int)$storeId . '
				AND 		`active` = 1
			');

		return $out;
	}

	public function getBetriebLeader($storeId)
	{
		return $this->qCol('
				SELECT 		t.`foodsaver_id`,
							t.`verantwortlich`

				FROM 		`fs_betrieb_team` t
				INNER JOIN  `fs_foodsaver` fs ON fs.id = t.foodsaver_id

				WHERE 		t.`betrieb_id` = ' . (int)$storeId . '
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

			FROM 		`fs_lebensmittel`
			ORDER BY `name`');
	}

	public function getBasics_kette()
	{
		return $this->q('
			SELECT 	 	`id`,
						`name`

			FROM 		`fs_kette`
			ORDER BY `name`');
	}

	public function listBetriebReq($bezirk_id)
	{
		return $this->q('
				SELECT 	fs_betrieb.id,
						`fs_betrieb`.betrieb_status_id,
						fs_betrieb.plz,
						fs_betrieb.added,
						`stadt`,
						fs_betrieb.kette_id,
						fs_betrieb.betrieb_kategorie_id,
						fs_betrieb.name,
						CONCAT(fs_betrieb.str," ",fs_betrieb.hsnr) AS anschrift,
						fs_betrieb.str,
						fs_betrieb.hsnr,
						CONCAT(fs_betrieb.lat,", ",fs_betrieb.lon) AS geo,
						fs_betrieb.`betrieb_status_id`,
						fs_bezirk.name AS bezirk_name

				FROM 	fs_betrieb,
						fs_bezirk

				WHERE 	fs_betrieb.bezirk_id = fs_bezirk.id
				AND 	fs_betrieb.bezirk_id IN(' . implode(',', $this->regionGateway->listIdsForDescendantsAndSelf($bezirk_id)) . ')

		');
	}

	public function update_betrieb($id, $data)
	{
		if (isset($data['lebensmittel']) && is_array($data['lebensmittel'])) {
			$this->del('
					DELETE FROM 	`fs_betrieb_has_lebensmittel`
					WHERE 			`betrieb_id` = ' . (int)$id . '
				');

			foreach ($data['lebensmittel'] as $lebensmittel_id) {
				$this->insert('
						INSERT INTO `fs_betrieb_has_lebensmittel`
						(
							`betrieb_id`,
							`lebensmittel_id`
						)
						VALUES
						(
							' . (int)$id . ',
							' . (int)$lebensmittel_id . '
						)
					');
			}
		}

		if (!isset($data['status_date'])) {
			$data['status_date'] = date('Y-m-d H:i:s');
		}

		$name = $data['name'];
		if ($tcid = $this->storeGateway->getBetriebConversation($id, false)) {
			$team_conversation_name = $this->translationHelper->sv('team_conversation_name', $name);
			$this->messagesGateway->renameConversation($tcid, $team_conversation_name);
		}
		if ($scid = $this->storeGateway->getBetriebConversation($id, true)) {
			$springer_conversation_name = $this->translationHelper->sv('springer_conversation_name', $name);
			$this->messagesGateway->renameConversation($scid, $springer_conversation_name);
		}

		return $this->update('
		UPDATE 	`fs_betrieb`

		SET 	`betrieb_status_id` =  ' . (int)$data['betrieb_status_id'] . ',
				`bezirk_id` =  ' . (int)$data['bezirk_id'] . ',
				`plz` =  ' . $this->strval($data['plz']) . ',
				`stadt` =  ' . $this->strval($data['stadt']) . ',
				`lat` =  ' . $this->strval($data['lat']) . ',
				`lon` =  ' . $this->strval($data['lon']) . ',
				`kette_id` =  ' . (int)$data['kette_id'] . ',
				`betrieb_kategorie_id` =  ' . (int)$data['betrieb_kategorie_id'] . ',
				`name` =  ' . $this->strval($name) . ',
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
				`public_time` =  ' . (int)$data['public_time'] . ',
				`ueberzeugungsarbeit` =  ' . (int)$data['ueberzeugungsarbeit'] . ',
				`presse` =  ' . (int)$data['presse'] . ',
				`sticker` =  ' . (int)$data['sticker'] . ',
				`abholmenge` =  ' . (int)$data['abholmenge'] . ',
				`prefetchtime` = ' . (int)$data['prefetchtime'] . '

		WHERE 	`id` = ' . (int)$id);
	}

	public function add_betrieb($data)
	{
		$id = $this->insert('
			INSERT INTO 	`fs_betrieb`
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
			`abholmenge`,
			`prefetchtime`
			)
			VALUES
			(
			' . (int)$data['betrieb_status_id'] . ',
			' . (int)$data['bezirk_id'] . ',
			NOW(),
			' . $this->strval($data['plz']) . ',
			' . $this->strval($data['stadt']) . ',
			' . $this->strval($data['lat']) . ',
			' . $this->strval($data['lon']) . ',
			' . (int)$data['kette_id'] . ',
			' . (int)$data['betrieb_kategorie_id'] . ',
			' . $this->strval($data['name']) . ',
			' . $this->strval($data['str']) . ',
			' . $this->strval($data['hsnr']) . ',
			' . $this->dateval($data['status_date']) . ',
			' . (int)$data['betrieb_status_id'] . ',
			' . $this->strval($data['ansprechpartner']) . ',
			' . $this->strval($data['telefon']) . ',
			' . $this->strval($data['fax']) . ',
			' . $this->strval($data['email']) . ',
			' . $this->dateval($data['begin']) . ',
			' . $this->strval($data['besonderheiten']) . ',
			' . $this->strval($data['public_info']) . ',
			' . (int)$data['public_time'] . ',
			' . (int)$data['ueberzeugungsarbeit'] . ',
			' . (int)$data['presse'] . ',
			' . (int)$data['sticker'] . ',
			' . (int)$data['abholmenge'] . ',
			' . (int)$data['prefetchtime'] . '
			)');

		if (isset($data['lebensmittel']) && is_array($data['lebensmittel'])) {
			foreach ($data['lebensmittel'] as $lebensmittel_id) {
				$this->insert('
						INSERT INTO `fs_betrieb_has_lebensmittel`
						(
							`betrieb_id`,
							`lebensmittel_id`
						)
						VALUES
						(
							' . (int)$id . ',
							' . (int)$lebensmittel_id . '
						)
					');
			}
		}

		if (isset($data['foodsaver']) && is_array($data['foodsaver'])) {
			foreach ($data['foodsaver'] as $foodsaver_id) {
				$this->insert('
						REPLACE INTO `fs_betrieb_team`
						(
							`betrieb_id`,
							`foodsaver_id`,
							`verantwortlich`,
							`active`,
							`stat_add_date`
						)
						VALUES
						(
							' . (int)$id . ',
							' . (int)$foodsaver_id . ',
							1,
							1,
							NOW()
						)
					');
			}
		}

		$this->createTeamConversation($id);
		$this->createSpringerConversation($id);

		return $id;
	}

	public function acceptRequest($fsid, $storeId)
	{
		$betrieb = $this->getVal('name', 'betrieb', $storeId);

		$this->bellGateway->addBell((int)$fsid, 'store_request_accept_title', 'store_request_accept', 'img img-store brown', [
			'href' => '/?page=fsbetrieb&id=' . (int)$storeId
		], [
			'user' => $this->session->user('name'),
			'name' => $betrieb
		], 'store-arequest-' . (int)$fsid);

		if ($scid = $this->storeGateway->getBetriebConversation($storeId, true)) {
			$this->messageModel->deleteUserFromConversation($scid, $fsid, true);
		}

		if ($tcid = $this->storeGateway->getBetriebConversation($storeId, false)) {
			$this->messageModel->addUserToConversation($tcid, $fsid, true);
		}

		return $this->update('
					UPDATE 	 	`fs_betrieb_team`
					SET 		`active` = 1, `stat_add_date` = NOW()
					WHERE 		`betrieb_id` = ' . (int)$storeId . '
					AND 		`foodsaver_id` = ' . (int)$fsid . '
		');
	}

	public function warteRequest($fsid, $storeId)
	{
		$betrieb = $this->getVal('name', 'betrieb', $storeId);

		$this->bellGateway->addBell((int)$fsid, 'store_request_accept_wait_title', 'store_request_accept_wait', 'img img-store brown', [
			'href' => '/?page=fsbetrieb&id=' . (int)$storeId
		], [
			'user' => $this->session->user('name'),
			'name' => $betrieb
		], 'store-wrequest-' . (int)$fsid);

		if ($scid = $this->storeGateway->getBetriebConversation($storeId, true)) {
			$this->messageModel->addUserToConversation($scid, $fsid, true);
		}

		return $this->update('
					UPDATE 	 	`fs_betrieb_team`
					SET 		`active` = 2
					WHERE 		`betrieb_id` = ' . (int)$storeId . '
					AND 		`foodsaver_id` = ' . (int)$fsid . '
		');
	}

	public function denyRequest($fsid, $storeId)
	{
		$betrieb = $this->getVal('name', 'betrieb', $storeId);

		$this->bellGateway->addBell((int)$fsid, 'store_request_deny_title', 'store_request_deny', 'img img-store brown', [
			'href' => '/?page=fsbetrieb&id=' . (int)$storeId
		], [
			'user' => $this->session->user('name'),
			'name' => $betrieb
		], 'store-drequest-' . (int)$fsid);

		return $this->update('
					DELETE FROM 	`fs_betrieb_team`
					WHERE 		`betrieb_id` = ' . (int)$storeId . '
					AND 		`foodsaver_id` = ' . (int)$fsid . '
		');
	}

	public function teamRequest($fsid, $storeId)
	{
		return $this->insert('
			REPLACE INTO `fs_betrieb_team`
			(
				`betrieb_id`,
				`foodsaver_id`,
				`verantwortlich`,
				`active`
			)
			VALUES
			(
				' . (int)$storeId . ',
				' . (int)$fsid . ',
				0,
				0
			)');
	}

	public function createTeamConversation($storeId)
	{
		$tcid = $this->messageModel->insertConversation([], true);
		$betrieb = $this->storeGateway->getMyStore($this->session->id(), $storeId);
		$team_conversation_name = $this->translationHelper->sv('team_conversation_name', $betrieb['name']);
		$this->messagesGateway->renameConversation($tcid, $team_conversation_name);

		$this->update('
				UPDATE	`fs_betrieb` SET team_conversation_id = ' . (int)$tcid . ' WHERE id = ' . (int)$storeId . '
			');

		$teamMembers = $this->storeGateway->getStoreTeam($storeId);
		if ($teamMembers) {
			foreach ($teamMembers as $fs) {
				$this->messageModel->addUserToConversation($tcid, $fs['id']);
			}
		}

		return $tcid;
	}

	public function createSpringerConversation($storeId)
	{
		$scid = $this->messageModel->insertConversation([], true);
		$betrieb = $this->storeGateway->getMyStore($this->session->id(), $storeId);
		$springer_conversation_name = $this->translationHelper->sv('springer_conversation_name', $betrieb['name']);
		$this->messagesGateway->renameConversation($scid, $springer_conversation_name);
		$this->update('
				UPDATE	`fs_betrieb` SET springer_conversation_id = ' . (int)$scid . ' WHERE id = ' . (int)$storeId . '
			');

		$springerMembers = $this->storeGateway->getBetriebSpringer($storeId);
		if ($springerMembers) {
			foreach ($springerMembers as $fs) {
				$this->messageModel->addUserToConversation($scid, $fs['id']);
			}
		}

		return $scid;
	}

	public function addTeamMessage($storeId, $message)
	{
		if ($betrieb = $this->storeGateway->getMyStore($this->session->id(), $storeId)) {
			if (!is_null($betrieb['team_conversation_id'])) {
				$this->messageModel->sendMessage($betrieb['team_conversation_id'], $message);
			} elseif (is_null($betrieb['team_conversation_id'])) {
				$tcid = $this->createTeamConversation($storeId);
				$this->messageModel->sendMessage($tcid, $message);
			}
		}
	}

	public function addBetriebTeam($storeId, $member, $verantwortlicher = false)
	{
		if (empty($member)) {
			return false;
		}
		if (!$verantwortlicher) {
			$verantwortlicher = [
				$this->session->id() => true
			];
		}

		$tmp = [];
		foreach ($verantwortlicher as $vv) {
			$tmp[$vv] = $vv;
		}
		$verantwortlicher = $tmp;

		$values = [];
		$member_ids = [];

		foreach ($member as $m) {
			$v = 0;
			if (isset($verantwortlicher[$m])) {
				$v = 1;
			}
			$member_ids[] = (int)$m;
			$values[] = '(' . (int)$storeId . ',' . (int)$m . ',' . $v . ',1,NOW())';
		}

		$this->del('DELETE FROM `fs_betrieb_team` WHERE `betrieb_id` = ' . (int)$storeId . ' AND active = 1 AND foodsaver_id NOT IN(' . implode(',', $member_ids) . ')');

		$sql = 'INSERT IGNORE INTO `fs_betrieb_team` (`betrieb_id`,`foodsaver_id`,`verantwortlich`,`active`,`stat_add_date`) VALUES ' . implode(',', $values);

		if ($cid = $this->storeGateway->getBetriebConversation($storeId)) {
			$this->messageModel->setConversationMembers($cid, $member_ids);
		}

		if ($sid = $this->storeGateway->getBetriebConversation($storeId, true)) {
			foreach ($verantwortlicher as $user) {
				$this->messageModel->addUserToConversation($sid, $user);
			}
		}

		if ($this->sql($sql)) {
			$this->update('
				UPDATE	`fs_betrieb_team` SET verantwortlich = 0 WHERE betrieb_id = ' . (int)$storeId . '
			');
			$this->update('
				UPDATE	`fs_betrieb_team` SET verantwortlich = 1 WHERE betrieb_id = ' . (int)$storeId . ' AND foodsaver_id IN(' . implode(',', $verantwortlicher) . ')
			');

			return true;
		}

		return false;
	}
}
