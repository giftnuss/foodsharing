<?php

namespace Foodsharing\Modules\Bell;

use Foodsharing\Lib\WebSocketConnection;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Bell\DTO\BellForExpirationUpdates;
use Foodsharing\Modules\Bell\DTO\BellForList;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;

class BellGateway extends BaseGateway
{
	/**
	 * @var WebSocketConnection
	 */
	private $webSocketConnection;

	public function __construct(Database $db, WebSocketConnection $webSocketConnection)
	{
		parent::__construct($db);

		$this->webSocketConnection = $webSocketConnection;
	}

	public function addBell($foodsavers, Bell $bellData): void
	{
		if (!is_array($foodsavers)) {
			$foodsavers = [$foodsavers];
		}

		$bellId = $this->db->insert(
			'fs_bell',
			[
				'name' => $bellData->title,
				'body' => $bellData->body,
				'vars' => $bellData->vars ? serialize($bellData->vars) : null,
				'attr' => $bellData->link_attributes ? serialize($bellData->link_attributes) : null,
				'icon' => $bellData->icon,
				'identifier' => $bellData->identifier,
				'time' => $bellData->time ? $bellData->time->format('Y-m-d H:i:s') : (new \DateTime())->format('Y-m-d H:i:s'),
				'closeable' => $bellData->closeable,
				'expiration' => $bellData->expiration ? $bellData->expiration->format('Y-m-d H:i:s') : null
			]
		);

		foreach ($foodsavers as $fs) {
			if (is_array($fs)) {
				$fs = $fs['id'];
			}

			$this->db->insert('fs_foodsaver_has_bell', ['foodsaver_id' => (int)$fs, 'bell_id' => $bellId, 'seen' => 0]);
			$this->updateFoodsaverClient((int)$fs);
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
	 * Method returns an array of all bells a user sees.
	 *
	 * @param $fsId
	 * @param string $limit
	 *
	 * @return BellForList[]
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
				b.`time`,
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
		$rows = $this->db->fetchAll($stm, [':foodsaver_id' => $fsId]);

		if (!$rows) {
			return [];
		}

		return $this->createBellsForListFromDatabaseRows($rows);
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
	 * @return BellForExpirationUpdates[]
	 */
	public function getExpiredByIdentifier(string $identifier): array
	{
		$bells = $this->db->fetchAll('
            SELECT
                `id`,
				`identifier`
            FROM `fs_bell`
            WHERE `identifier` LIKE :identifier
            AND `expiration` < NOW()',
			[':identifier' => $identifier]
		);

		return $this->createBellsForExpirationUpdatesFromDatabaseRows($bells);
	}

	public function bellWithIdentifierExists(string $identifier): bool
	{
		return $this->db->exists('fs_bell', ['identifier' => $identifier]);
	}

	public function delBellForFoodsaver($bellId, $fsId): void
	{
		$bellIsCloseable = $this->db->fetchValueByCriteria('fs_bell', 'closeable', ['id' => (int)$bellId]);

		if (!$bellIsCloseable) {
			return;
		}

		$this->db->delete('fs_foodsaver_has_bell', ['bell_id' => (int)$bellId, 'foodsaver_id' => (int)$fsId]);
		$this->updateFoodsaverClient($fsId);
	}

	public function delBellsByIdentifier($identifier): void
	{
		$foodsaverIds = $this->db->fetchAllValues(
			'SELECT DISTINCT `foodsaver_id`
			FROM `fs_foodsaver_has_bell` JOIN `fs_bell`
			ON `fs_foodsaver_has_bell`.bell_id = `fs_bell`.id
			WHERE `identifier` = :identifier',
			[':identifier' => $identifier]
		);

		$this->db->delete('fs_bell', ['identifier' => $identifier]);

		$this->updateMultipleFoodsaverClients($foodsaverIds);
	}

	public function setBellsAsSeen(array $bellIds, int $foodsaverId): void
	{
		$this->db->update('fs_foodsaver_has_bell',
			['seen' => 1],
			[
				'bell_id' => array_map('intval', $bellIds),
				'foodsaver_id' => $foodsaverId
			]
		);
	}

	private function updateFoodsaverClient(int $foodsaverId): void
	{
		$this->webSocketConnection->sendSock($foodsaverId, 'bell', 'update', []);
	}

	/**
	 * @param int[] $foodsaverIds
	 */
	private function updateMultipleFoodsaverClients(array $foodsaverIds): void
	{
		$this->webSocketConnection->sendSockMulti($foodsaverIds, 'bell', 'update', []);
	}

	/**
	 * @param array $databaseRows - 2D-array with bell data, expects indexes []['vars'] and []['attr'] to contain serialized data
	 *
	 * @return BellForList[] - BellData objects with with unserialized $ball->vars and $bell->attr
	 */
	private function createBellsForListFromDatabaseRows(array $databaseRows): array
	{
		$output = [];
		foreach ($databaseRows as $row) {
			$bellDTO = new BellForList();

			// This onclick-to-href conversion is probably not needed anymore
			if (isset($row['attr']['onclick'])) {
				preg_match('/profile\((.*?)\)/', $row['attr']['onclick'], $matches);
				if ($matches) {
					$row['attr']['href'] = '/profile/' . $matches[1];
				}
			}

			$bellDTO->id = $row['id'];
			$bellDTO->key = $row['body'];
			$bellDTO->title = $row['name'];
			$bellDTO->payload = unserialize($row['vars'], ['allowed_classes' => false]);
			$bellDTO->href = unserialize($row['attr'], ['allowed_classes' => false])['href'];
			$bellDTO->icon = $row['icon'][0] != '/' ? $row['icon'] : null;
			$bellDTO->image = $row['icon'][0] == '/' ? $row['icon'] : null;
			$bellDTO->createdAt = (new \DateTime($row['time']))->format('Y-m-d\TH:i:s');
			$bellDTO->isRead = $row['seen'];
			$bellDTO->isCloseable = $row['closeable'];

			$output[] = $bellDTO;
		}

		return $output;
	}

	/**
	 * @param array $databaseRows - 2D-array with bell data, expects indexes []['vars'] and []['attr'] to contain serialized data
	 *
	 * @return BellForExpirationUpdates[] - BellData objects with with unserialized $ball->vars and $bell->attr
	 */
	private function createBellsForExpirationUpdatesFromDatabaseRows(array $databaseRows): array
	{
		$output = [];
		foreach ($databaseRows as $row) {
			$bellDTO = new BellForExpirationUpdates();

			$bellDTO->id = $row['id'];
			$bellDTO->identifier = $row['identifier'];

			$output[] = $bellDTO;
		}

		return $output;
	}
}
