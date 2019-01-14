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
	/**
	 * delete a conversation if the last message is empty.
	 *
	 * @param $cid int conversation id
	 *
	 * @return int
	 */
	public function deleteEmptyConversation(int $cid): int
	{
		$deletedRows = 0;

		$countEmpty = (int)$this->db->count('fs_conversation', [
			'id' => $cid,
			'last_message' => null]);

		if ($countEmpty > 0) {
			$deletedRows = $this->db->delete('fs_foodsaver_has_conversation', ['conversation_id' => $cid]);
			$this->db->delete('fs_conversation', ['id' => $cid]);
		}

		return $deletedRows;
	}
}
