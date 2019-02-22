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
	/**
	 * @var BellUpdateTrigger
	 */
	private $bellUpdateTrigger;

	public function __construct(Database $db, WebSocketSender $webSocketSender, BellUpdateTrigger $bellUpdateTrigger)
	{
		parent::__construct($db);

		$this->webSocketSender = $webSocketSender;
		$this->bellUpdateTrigger = $bellUpdateTrigger;
	}

	/**
	 * @param int|int[] $foodsaver_ids
	 * @param string[] $link_attributes
	 * @param string[] $vars
	 * @param \DateTime $expiration A DateTime object that defines when the time since when the bell will be outdated - null means it doesn't expire
	 * @param \DateTime $time A DateTime object for the bell's time - null means current date and time
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
		\DateTime $expiration = null,
		\DateTime $time = null
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

		if ($time === null) {
			$time = new \DateTime();
		}

		$bid = $this->db->insert(
			'fs_bell',
			[
				'name' => $title,
				'body' => $body,
				'vars' => $vars,
				'attr' => $link_attributes,
				'icon' => $icon,
				'identifier' => $identifier,
				'time' => $time->format('Y-m-d H:i:s'),
				'closeable' => $closeable,
				'expiration' => $expiration ? $expiration->format('Y-m-d H:i:s') : null
			]
		);

		foreach ($foodsaver_ids as $id) {
			if (is_array($id)) {
				$id = $id['id'];
			}

			$this->db->insert('fs_foodsaver_has_bell', ['foodsaver_id' => (int)$id, 'bell_id' => $bid, 'seen' => 0]);
			$this->updateFoodsaverClient((int)$id);
		}
	}

	/**
	 * @param array $data - the data to be updated. $data['var'] and data['attr'] must not be serialized.
	 */
	public function updateBell(int $bellId, array $data, bool $setUnseen = false, bool $updateClients = true): void
	{
		if (isset($data['attr'])) {
			$data['attr'] = serialize($data['attr']);
		}

		if (isset($data['vars'])) {
			$data['vars'] = serialize($data['vars']);
		}

		if (isset($data['time']) && is_a($data['time'], \DateTime::class)) {
			$data['time'] = $data['time']->format('Y-m-d H:i:s');
		}

		if (isset($data['expiration']) && is_a($data['expiration'], \DateTime::class)) {
			$data['expiration'] = $data['expiration']->format('Y-m-d H:i:s');
		}

		$this->db->update('fs_bell', $data, ['id' => $bellId]);

		$foodsaverIds = $this->db->fetchAllValuesByCriteria('fs_foodsaver_has_bell', 'foodsaver_id', ['bell_id' => $bellId]);

		if ($setUnseen && !empty($foodsaverIds)) {
			$this->db->update('fs_foodsaver_has_bell', ['seen' => 0], ['foodsaver_id' => $foodsaverIds, 'bell_id' => $bellId]);
		}

		if ($updateClients) {
			$this->updateMultipleFoodsaverClients($foodsaverIds);
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
			return $this->unserializeBells($bells);
		}

		return [];
	}

	/**
	 * @param string $identifier - can contain SQL wildcards
	 *
	 * @return int - id of the bell
	 */
	public function getOneByIdentifier(string $identifier): int
	{
		return $this->db->fetchValueByCriteria('fs_bell', 'id', ['identifier like' => $identifier]);
	}

	/**
	 * @param string $identifier - can contain SQL wildcards
	 *
	 * @return array - [index => ['id', 'name', 'body', 'vars', 'attr', 'icon', 'identifier', 'time', 'time_ts', 'closable']
	 */
	public function getExpiredByIdentifier(string $identifier): array
	{
		$bells = $this->db->fetchAll('
            SELECT
                `id`,
				`name`,
				`body`,
				`vars`,
				`attr`,
				`icon`,
				`identifier`,
				`time`,
				UNIX_TIMESTAMP(`time`) AS time_ts,
				`closeable`
            FROM `fs_bell`
            WHERE `identifier` LIKE :identifier
            AND `expiration` < NOW()',
			[':identifier' => $identifier]
		);

		return $this->unserializeBells($bells);
	}

	public function bellWithIdentifierExists(string $identifier): bool
	{
		return $this->db->exists('fs_bell', ['identifier' => $identifier]);
	}

	public function delBellForFoodsaver($id, $fsId): void
	{
		$bellIsCloseable = $this->db->fetchValueByCriteria('fs_bell', 'closeable', ['id' => (int)$id]);

		if (!$bellIsCloseable) {
			return;
		}

		$this->db->delete('fs_foodsaver_has_bell', ['bell_id' => (int)$id, 'foodsaver_id' => (int)$fsId]);
		$this->updateFoodsaverClient($fsId);
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

		$this->updateMultipleFoodsaverClients($foodsaverIds);
	}

	public function setBellsAsSeen(array $bids, int $foodsaverId): void
	{
		$this->db->execute(
			'UPDATE `fs_foodsaver_has_bell` SET `seen` = 1 WHERE `bell_id` IN (' . implode(',', array_map('intval', $bids)) . ') AND `foodsaver_id` =:fsId',
			['fsId' => $foodsaverId]
		);
	}

	private function updateFoodsaverClient(int $foodsaverId): void
	{
		$this->webSocketSender->sendSock($foodsaverId, 'bell', 'update', []);
	}

	/**
	 * @param int[] $foodsaverIds
	 */
	private function updateMultipleFoodsaverClients(array $foodsaverIds): void
	{
		$this->webSocketSender->sendSockMulti($foodsaverIds, 'bell', 'update', []);
	}

	/**
	 * @param array $bells - 2D-array with bell data, needs indexes []['vars'] and []['attr'] to contain serialized data
	 *
	 * @return array - array with the same structure as the input, but with unserialized []['vars'] and []['attr']
	 */
	private function unserializeBells(array $bells): array
	{
		foreach ($bells as $i => $iValue) {
			if (!empty($bells[$i]['vars'])) {
				$bells[$i]['vars'] = unserialize($bells[$i]['vars'], array('allowed_classes' => false));
			}

			if (!empty($bells[$i]['attr'])) {
				$bells[$i]['attr'] = unserialize($bells[$i]['attr'], array('allowed_classes' => false));
			}
		}

		return $bells;
	}
}
