<?php
/**
 * standard gateway for the message component providing database queries. Available in other classes through symfony's autowiring.
 *
 * @see https://symfony.com/doc/current/service_container/autowiring.html Defining Services Dependencies Automatically (Autowiring)
 * User: pmayd
 * Created: 2019-01-13
 * Last Change: 2019-01-14
 */

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Store\StoreGateway;

final class MessageGateway extends BaseGateway
{
	/**
	 * @var StoreGateway
	 */
	private $storeGateway;

	public function __construct(Database $db, StoreGateway $storeGateway)
	{
		parent::__construct($db);
		$this->storeGateway = $storeGateway;
	}

	public function getConversationName(int $conversationId): ?string
	{
		return $this->db->fetchValueByCriteria('fs_conversation', 'name', ['id' => $conversationId]);
	}

	public function getConversationMemberNames(int $conversationId): array
	{
		return $this->db->fetchAllValues(
			'SELECT fs.name FROM fs_foodsaver_has_conversation fc, fs_foodsaver fs WHERE fs.id = fc.foodsaver_id AND fc.conversation_id = :id AND fs.deleted_at IS NULL',
			['id' => $conversationId]
		);
	}

	public function getConversationMessages(int $conversation_id, int $limit = 20, int $offset = 0): array
	{
		return $this->db->fetchAll('
			SELECT
				m.id,
				fs.`id` AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				m.`body`,
				m.`time`

			FROM
				`fs_msg` m,
				`fs_foodsaver` fs

			WHERE
				m.foodsaver_id = fs.id

			AND
				m.conversation_id = :id

			ORDER BY
				m.`time` DESC

			LIMIT :offset, :limit
		', [
			'id' => $conversation_id,
			'offset' => $offset,
			'limit' => $limit,
		]);
	}

	/**
	 * There are different ways conversations can be named: Some groups have actual names, then you want to display the
	 * name, some groups have not, so you want to display a list of all Members, some groups belong to a store so you want
	 * to display the store name and if the group has only two people, you want to display the name of the other person.
	 * This function gives you the correct one so you don't have to worry.
	 *
	 * @param int $foodsaverId - the foodsaver the name should be displayed to
	 * @param int $conversationId - the id of the conversation
	 */
	public function getProperConversationNameForFoodsaver(int $foodsaverId, int $conversationId): string
	{
		$name = $this->getConversationName($conversationId);

		if ($name !== null) {
			return $name;
		}

		// Maybe it's a store converstation
		$storeName = $this->storeGateway->getStoreNameByConversationId($conversationId);

		if ($storeName !== null) {
			return 'Betrieb ' . $storeName;
		}

		$conversationMembers = $this->getConversationMemberNames($conversationId);
		$foodsaverName = $this->db->fetchValueByCriteria('fs_foodsaver', 'name', ['id' => $foodsaverId]);

		// the foodsaver knows their name, so it can be removed
		$conversationMembersRelevantForFoodsaver = array_diff($conversationMembers, [$foodsaverName]);
		if (count($conversationMembers) > 2) {
			// in conversations with more than 2 members, there should still be something representing the foodsaver
			$conversationMembersRelevantForFoodsaver[] = 'Du';
		}

		return implode(', ', $conversationMembersRelevantForFoodsaver);
	}

}
