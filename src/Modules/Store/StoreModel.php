<?php

namespace Foodsharing\Modules\Store;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Modules\Region\RegionGateway;

class StoreModel extends Db
{
	private $bellGateway;
	private $storeGateway;
	private $regionGateway;
	private $messageGateway;

	public function __construct(
		BellGateway $bellGateway,
		StoreGateway $storeGateway,
		RegionGateway $regionGateway,
		MessageGateway $messageGateway
	) {
		$this->bellGateway = $bellGateway;
		$this->storeGateway = $storeGateway;
		$this->regionGateway = $regionGateway;
		$this->messageGateway = $messageGateway;

		parent::__construct();
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

	public function addBetriebTeam(int $storeId, array $member, array $selectedManagers)
	{
		if (empty($member)) {
			return false;
		}

		$values = [];
		$memberIds = [];
		$managerIds = []; // intersection between members and selectedManagers

		foreach ($member as $m) {
			$v = 0;
			if (in_array($m, $selectedManagers)) {
				$v = 1;
				$managerIds[] = $m;
			}
			$memberIds[] = (int)$m;
			$values[] = '(' . $storeId . ',' . (int)$m . ',' . $v . ',1,NOW())';
		}

		$this->del('
			DELETE FROM `fs_betrieb_team`
			WHERE `betrieb_id` = ' . $storeId . '
			AND active = 1
			AND foodsaver_id NOT IN (' . implode(',', $memberIds) . ')
		');

		if ($teamChatId = $this->storeGateway->getBetriebConversation($storeId)) {
			$this->messageGateway->setConversationMembers($teamChatId, $memberIds);
		}

		if ($jumperChatId = $this->storeGateway->getBetriebConversation($storeId, true)) {
			$jumper = $this->storeGateway->getBetriebSpringer($storeId);
			$standbyTeam = array_merge($managerIds, array_column($jumper, 'id'));
			$this->messageGateway->setConversationMembers($jumperChatId, $standbyTeam);
		}

		$sql = 'INSERT IGNORE INTO `fs_betrieb_team` (`betrieb_id`,`foodsaver_id`,`verantwortlich`,`active`,`stat_add_date`) VALUES ' . implode(',', $values);

		if ($this->sql($sql)) {
			$this->update('
				UPDATE	`fs_betrieb_team` SET verantwortlich = 0 WHERE betrieb_id = ' . $storeId . '
			');
			$this->update('
				UPDATE	`fs_betrieb_team` SET verantwortlich = 1 WHERE betrieb_id = ' . $storeId . ' AND foodsaver_id IN(' . implode(',', $managerIds) . ')
			');

			return true;
		}

		return false;
	}
}
