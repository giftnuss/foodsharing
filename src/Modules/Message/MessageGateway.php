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

final class MessageGateway extends BaseGateway
{
	public function getConversationName(int $conversationId): ?string
	{
		return $this->db->fetchValueByCriteria('fs_conversation', 'name', ['id' => $conversationId]);
	}

	public function getConversationMemberNames(int $conversationId): array
	{
		$members = $this->db->fetchAll(
			'SELECT fs.name FROM fs_foodsaver_has_conversation fc, fs_foodsaver fs WHERE fs.id = fc.foodsaver_id AND fc.conversation_id = :id AND fs.deleted_at IS NULL',
			['id' => $conversationId]
		);

		return array_map(function ($member) { return $member['name']; }, $members);
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
}
