<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Lib\Db\Db;

final class MessageModel extends Db
{
	public function updateConversation($cid, $last_fs_id, $body, $last_message_id): bool
	{
		return $this->update('
				UPDATE
					`fs_conversation`

				SET
					`last` = NOW(),
					`last_foodsaver_id` = ' . (int)$last_fs_id . ',
					`last_message` = ' . $this->strval($body) . ',
					`last_message_id` = ' . (int)$last_message_id . '

				WHERE
					`id` = ' . (int)$cid . '
		');
	}

	public function findConnectedPeople($term)
	{
		$out = array();

		// for orga and bot-welcome team, allow to contact everyone who is foodsaver
		if ($this->session->may('orga') || (isset($_SESSION['client']['bezirke']) && is_array($_SESSION['client']['bezirke']) && in_array(813, $_SESSION['client']['bezirke']))) {
			$sql = '
                SELECT 
                    fs.id AS id,
                    CONCAT(fs.name," ",fs.nachname ) AS value
                    
				FROM
                    fs_foodsaver fs
                    
				WHERE
                    fs.rolle >= 1
                    
				AND
                    CONCAT(fs.name," ",fs.nachname ) LIKE "%' . $this->safe($term) . '%"
                    
				GROUP BY
					fs.id
				';
			if ($user = $this->q($sql)) {
				$out = array_merge($out, $user);
			}
		} elseif (isset($_SESSION['client']['bezirke']) && is_array($_SESSION['client']['bezirke']) && count($_SESSION['client']['bezirke']) > 0) {
			// add user to regions and groups
			$ids = array();
			foreach ($_SESSION['client']['bezirke'] as $i => $bezirk) {
				$ids[] = $bezirk['id'];
			}

			$sql = '
				SELECT
					DISTINCT fs.id AS id,
					CONCAT(fs.name," ",fs.nachname ) AS value

				FROM
					fs_foodsaver fs,
					fs_foodsaver_has_bezirk hb

				WHERE
					hb.foodsaver_id = fs.id

				AND
					hb.bezirk_id IN(' . implode(',', $ids) . ')

				AND
                    CONCAT(fs.name," ",fs.nachname ) LIKE "%' . $this->safe($term) . '%"
                    
				AND
					fs.deleted_at IS NULL
			';

			if ($user = $this->q($sql)) {
				$out = array_merge($out, $user);
			}
		}

		return $out;
	}

	/**
	 * check if there are unread messages in conversation give back the conversation ids.
	 */
	public function checkConversationUpdates()
	{
		/*
		 * for more speed check the memcache first
		 */

		/*
		 * Memcache var is set but no updates
		 */
		$cache = $this->mem->user($this->session->id(), 'msg-update');

		if ($cache === 0) {
			return false;
		}

		if (is_array($cache)) {
			$this->mem->userSet($this->session->id(), 'msg-update', 0);

			return $cache;
		}  /*
		 * Memcache is not set so get conversation IDs directly from db
		 */

		$this->mem->userSet($this->session->id(), 'msg-update', 0);

		return $this->getUpdatedConversationIds();
	}

	private function getUpdatedConversationIds()
	{
		return $this->qCol('SELECT conversation_id FROM fs_foodsaver_has_conversation WHERE foodsaver_id = ' . (int)$this->session->id() . ' AND unread = 1');
	}

	/**
	 * set conversations as read.
	 *
	 * @param array $conv_ids
	 *
	 * @return bool | int
	 */
	public function sendMessage($cid, $body, $sender_id = false)
	{
		if (!$sender_id) {
			$sender_id = $this->session->id();
		} else {
			$sender_id = (int)$sender_id;
		}

		$mid = $this->insert('
            INSERT INTO 
                `fs_msg`(`conversation_id`, `foodsaver_id`, `body`, `time`) 

            VALUES 
                (' . (int)$cid . ',' . $sender_id . ',' . $this->strval($body) . ',NOW())
        ');
		if ($mid) {
			$this->update('
                UPDATE 
                    `fs_foodsaver_has_conversation` 

                SET 
                    unread = 1 

                WHERE 
                    conversation_id = ' . (int)$cid . '
                     
                AND 
                    `foodsaver_id` != ' . (int)$sender_id
			);
			$this->updateConversation($cid, $sender_id, $body, $mid);

			return $mid;
		}

		return false;
	}

	private function updateDenormalizedConversationData($cids = false): array
	{
		if ($cids === false) {
			$cids = $this->qCol('SELECT id FROM fs_conversation');
		} elseif (!is_array($cids)) {
			$cids = array($cids);
		}
		$members = array();
		foreach ($cids as $id) {
			$member = $this->listConversationMembers($id);

			/*
			 * UPDATE conversation
			 */
			$this->update('
				UPDATE
					`fs_conversation`

				SET
					`member` = ' . $this->strval(serialize($member)) . '

				WHERE
					`id` = ' . (int)$id . '
			');

			$members[] = $member;
		}

		return $members;
	}

	public function setConversationMembers($cid, $fsids, $unread = false)
	{
		if ((int)$cid > 0) {
			$ur = 0;
			if ($unread) {
				$ur = 1;
			}

			if (count($fsids) < 1) {
				$this->del('DELETE FROM `fs_foodsaver_has_conversation` WHERE conversation_id = ' . (int)$cid);
			} else {
				$ids = implode(',', $fsids);
				$this->del('DELETE FROM `fs_foodsaver_has_conversation` WHERE conversation_id = ' . (int)$cid . ' AND foodsaver_id NOT IN (' . $ids . ')');
				$values = array();
				foreach ($fsids as $user) {
					$values[] = '(' . (int)$cid . ', ' . (int)$user . ', ' . $ur . ')';
				}
				if (count($values) > 0) {
					$this->insert('INSERT IGNORE INTO `fs_foodsaver_has_conversation` (conversation_id, foodsaver_id, unread) VALUES ' . implode(',', $values));
				}
			}

			$this->updateDenormalizedConversationData($cid);
		}
	}

	public function addUserToConversation($cid, $fsid, $unread = false): void
	{
		$ur = 0;
		if ($unread) {
			$ur = 1;
		}

		$this->insert('INSERT IGNORE INTO `fs_foodsaver_has_conversation` (conversation_id, foodsaver_id, unread) VALUES (' . (int)$cid . ', ' . (int)$fsid . ', ' . $ur . ')');
		$this->updateDenormalizedConversationData($cid);
	}

	public function add_message($data): bool
	{
		if ($cid = $this->addConversation(array($data['sender_id'] => $data['sender_id'], $data['recip_id'] => $data['recip_id']), false, false)) {
			$this->sendMessage($cid, $data['msg'], $data['sender_id']);

			return true;
		}

		return false;
	}

	public function message($recip_id, $message)
	{
		if ($conversation_id = $this->user2conv($recip_id)) {
			return $this->sendMessage($conversation_id, $message);
		}

		return false;
	}
}
