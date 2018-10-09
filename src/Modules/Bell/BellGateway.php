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

	/**
	 * @param int|int[] $foodsaver_ids
	 * @param string[] $link_attributes
	 * @param string[] $vars
	 * @param int|null $timestamp A unix timestamp for the bell's time - null means current date and time
	 */
	public function addBell(
		$foodsaver_ids,
		string $title,
		string $body,
		string $icon,
		array $link_attributes,
		array $vars,
		string $identifier = '',
		int $closeable = 1,
		?int $timestamp = null
	): void {
		if (!is_array($foodsaver_ids)) {
			$foodsaver_ids = array($foodsaver_ids);
		}

		if ($link_attributes !== false) {
			$link_attributes = serialize($link_attributes);
		}

		if ($vars !== false) {
			$vars = serialize($vars);
		}

		if ($timestamp === null) {
			$timestamp = time();
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
				'time' => date('Y-m-d H:i:s', $timestamp),
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
	 * @param string[] $link_attributes
	 * @param string[] $vars
	 * @param int|null $timestamp A unix timestamp for the bells time - null means current date and time
	 */
	public function updateBell(
		int $bellId,
		string $title,
		string $body,
		string $icon,
		array $link_attributes,
		array $vars,
		string $identifier = '',
		int $closeable = 1,
		?int $timestamp = null,
		bool $setUnseen = false
	): void {
		if ($link_attributes !== false) {
			$link_attributes = serialize($link_attributes);
		}

		if ($vars !== false) {
			$vars = serialize($vars);
		}

		if ($timestamp === null) {
			$timestamp = time();
		}

		$this->db->update(
			'fs_bell',
			[
				'name' => strip_tags($title),
				'body' => strip_tags($body),
				'vars' => strip_tags($vars),
				'attr' => strip_tags($link_attributes),
				'icon' => strip_tags($icon),
				'identifier' => strip_tags($identifier),
				'time' => date('Y-m-d H:i:s', $timestamp),
				'closeable' => $closeable
			],
			['id' => $bellId]
		);

		$foodsaverIds = $this->db->fetchAllValuesByCriteria('fs_foodsaver_has_bell', 'foodsaver_id', ['bell_id' => $bellId]);

		if ($setUnseen) {
			$this->db->update('fs_foodsaver_has_bell', ['seen' => 0], ['foodsaver_id' => $foodsaverIds, 'bell_id' => $bellId]);
		}

		$this->notifyFoodsavers($foodsaverIds);
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

	public function getOneByIdentifier(string $identifier): int
	{
		return $this->db->fetchValueByCriteria('fs_bell', 'id', ['identifier' => $identifier]);
	}

	public function bellWithIdentifierExists(string $identifier): bool
	{
		return $this->db->exists('fs_bell', ['identifier' => $identifier]);
	}

	public function delBellForFoodsaver($id, $fsId): int
	{
		$result = $this->db->delete('fs_foodsaver_has_bell', ['bell_id' => (int)$id, 'foodsaver_id' => (int)$fsId]);
		$this->notifyFoodsaver($fsId);

		return $result;
	}

	public function delBellsByIdentifier($identifier): void
	{
		$foodsaverIds = $this->db->fetchAllValues(
			'SELECT `foodsaver_id` 
            FROM `fs_foodsaver_has_bell` JOIN `fs_bell` 
            WHERE `identifier` = :identifier',
			[':identifier' => $identifier]
		);

		$this->db->delete('fs_bell', ['identifier' => $identifier]);

		$this->notifyFoodsavers($foodsaverIds);
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

	/**
	 * @param int[] $foodsaverIds
	 */
	private function notifyFoodsavers(array $foodsaverIds): void
	{
		$this->webSocketSender->sendSockMulti($foodsaverIds, 'bell', 'notify', []);
	}
}
