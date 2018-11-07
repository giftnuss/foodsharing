<?php

namespace Foodsharing\Modules\Bell;

use Foodsharing\Lib\WebSocketSender;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;

class BellGateway extends BaseGateway
{
	/**
	 * @var WebSocketSender
	 */
	private $webSocketSender;

	public function __construct(Database $db, WebSocketSender $webSocketSender)
	{
		parent::__construct($db);

		$this->webSocketSender = $webSocketSender;
	}

	public function addBell($foodsaver_ids, $title, $body, $icon, $link_attributes, $vars, $identifier = '', $closeable = 1): void
	{
		if (!is_array($foodsaver_ids)) {
			$foodsaver_ids = array($foodsaver_ids);
		}

		if ($link_attributes !== false) {
			$link_attributes = serialize($link_attributes);
		}

		if ($vars !== false) {
			$vars = serialize($vars);
		}

		$bid = $this->db->insert(
			'fs_bell',
			[
				'name' => strip_tags($title),
				'body' => strip_tags($body),
				'vars' => strip_tags($vars),
				'attr' => strip_tags($link_attributes),
				'icon' => strip_tags($icon),
				'identifier' => strip_tags($identifier),
				'time' => date('Y-m-d H:i:s'),
				'closeable' => $closeable
			]
		);

		$values = array();
		foreach ($foodsaver_ids as $id) {
			if (is_array($id)) {
				$id = $id['id'];
			}

			$this->db->insert('fs_foodsaver_has_bell', ['foodsaver_id' => (int)$id, 'bell_id' => $bid, 'seen' => 0]);
			$this->notifyFoodsaver((int)$id);
		}
	}

	/**
	 * Method returns an array of all conversation from the user.
	 *
	 * @param $fsId
	 * @param string $limit
	 *
	 * @return array|bool
	 */
	public function listBells($fsId, $limit = '')
	{
		if ($limit !== '') {
			$limit = ' LIMIT 0,' . (int)$limit;
		}

		$stm = '
			SELECT
				b.`id`,
				b.`name`, 
				b.`body`, 
				b.`vars`, 
				b.`attr`, 
				b.`icon`, 
				b.`identifier`, 
				b.`time`,
				UNIX_TIMESTAMP(b.`time`) AS time_ts,
				hb.seen,
				b.closeable
	
			FROM
				fs_bell b,
				`fs_foodsaver_has_bell` hb
	
			WHERE
				hb.bell_id = b.id
	
			AND
				hb.foodsaver_id = :foodsaver_id
	
			ORDER BY b.`time` DESC
			' . $limit . '
		';
		if ($bells = $this->db->fetchAll($stm, [':foodsaver_id' => $fsId])
		) {
			$ids = array();
			foreach ($bells as $i => $iValue) {
				$ids[] = (int)$bells[$i]['id'];

				if (!empty($bells[$i]['vars'])) {
					$bells[$i]['vars'] = unserialize($bells[$i]['vars'], array('allowed_classes' => false));
				}

				if (!empty($bells[$i]['attr'])) {
					$bells[$i]['attr'] = unserialize($bells[$i]['attr'], array('allowed_classes' => false));
				}
			}

			return $bells;
		}

		return [];
	}

	public function getStoreBells($bids): array
	{
		$stm = '
			SELECT COUNT( b.id ) AS count, b.name, b.id, MIN( a.date ) AS `date`, UNIX_TIMESTAMP(MIN( a.date )) AS date_ts
			FROM `fs_betrieb` b, fs_abholer a	
			WHERE a.betrieb_id = b.id AND a.betrieb_id IN(' . implode(',', $bids) . ') AND	a.confirmed = 0  AND a.`date` > NOW() 
			GROUP BY b.id';

		return $this->db->fetchAll($stm);
	}

	public function getFairteilerBells($bids): array
	{
		if ($bids) {
			return $this->db->fetchAll('
				SELECT 	
					ft.id,
					ft.`bezirk_id`,
					bz.name AS bezirk_name,
					ft.`name`,
					ft.`add_date`,
					UNIX_TIMESTAMP(ft.`add_date`) AS time_ts
				
				FROM 	
					fs_fairteiler ft,
					fs_bezirk bz						
					
				WHERE 	ft.bezirk_id = bz.id
				AND 	ft.status = 0
				AND 	ft.bezirk_id IN(' . implode(',', $bids) . ')');
		}

		return [];
	}

	public function delBellForFoodsaver($id, $fsId): int
	{
		return $this->db->delete('fs_foodsaver_has_bell', ['bell_id' => (int)$id, 'foodsaver_id' => (int)$fsId]);
	}

	public function delBellsByIdentifier($identifier): void
	{
		$this->db->delete('fs_bell', ['identifier' => $identifier]);
	}

	public function setBellsAsSeen(array $bids, int $foodsaverId): void
	{
		$stm = 'UPDATE `fs_foodsaver_has_bell` SET `seen` = 1 WHERE `bell_id` IN (' . implode(',', $bids) . ') AND `foodsaver_id` = ' . $foodsaverId;
		$this->db->execute($stm);
	}

	private function notifyFoodsaver(int $foodsaverId): void
	{
		$this->webSocketSender->sendSock($foodsaverId, 'bell', 'notify', []);
	}
}
