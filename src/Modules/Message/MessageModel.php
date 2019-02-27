<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Lib\Db\Db;

final class MessageModel extends Db
{
	public function user2conv($fsid)
	{
		return $this->addConversation(array($fsid => $fsid));
	}

	/**
	 * Adds a new Conversation but first check if is there allready an conversation with exaclty this user_ids
	 * Does not include locked conversations as those may be automatically changed.
	 *
	 * @param array $recips
	 * @param string $body
	 */
	public function addConversation($recips, $body = false, $own = true)
	{
		/*
		 * add the current user to the recipients
		 */
		if ($own) {
			$recips[(int)$this->session->id()] = (int)$this->session->id();
		}

		/*
		 * make sure the order of this array
		 */
		ksort($recips);

		$conversation_id = false;

		$cids = $this->qCol('
            SELECT
                hc.conversation_id
                 
            FROM
                `fs_foodsaver_has_conversation` hc
                 
            LEFT JOIN
                `fs_conversation` c
                 
            ON
                c.id = hc.conversation_id

            WHERE
                hc.`foodsaver_id` = ' . (int)$this->session->id() . ' 
                
            AND 
                c.locked = 0
		');
		if ($cids) {
			$sql = '
                SELECT
                  conversation_id,
                  GROUP_CONCAT(foodsaver_id ORDER BY foodsaver_id SEPARATOR ":") AS idstring
        
                FROM
                  fs_foodsaver_has_conversation
        
                WHERE
                  conversation_id IN (' . implode(',', $cids) . ')
        
                GROUP BY
                  conversation_id
        
                HAVING
                  idstring = "' . implode(':', array_map('intval', $recips)) . '"
		    ';

			if ($conv = $this->qRow($sql)) {
				$conversation_id = $conv['conversation_id'];
			}
		}

		/*
		 * If we dont have an existing conversation create a new one
		*/
		if (!$conversation_id) {
			$conversation_id = $this->insertConversation($recips, false, $body !== false);
		}

		if ($body !== false) {
			$this->sendMessage($conversation_id, $body, $this->session->id());
		}

		return $conversation_id;
	}

	/**
	 * Renames an Conversation.
	 */
	public function renameConversation($cid, $name): bool
	{
		return $this->update('UPDATE fs_conversation SET name = ' . $this->strval($name) . ' WHERE id = ' . (int)$cid);
	}

	public function conversationLocked($cid)
	{
		return $this->qOne('SELECT locked FROM fs_conversation WHERE id = ' . (int)$cid);
	}

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

	public function listConversationMembers($conversation_id): array
	{
		return $this->q('
			SELECT
				fs.id,
				fs.name,
				fs.photo,
				fs.email,
				fs.geschlecht

			FROM
                `fs_foodsaver_has_conversation` hc
                
			INNER JOIN
				`fs_foodsaver` fs ON fs.id = hc.foodsaver_id

			WHERE
				hc.conversation_id = ' . (int)$conversation_id . ' AND
				fs.deleted_at IS NULL
		');
	}

	public function wantMsgEmailInfo($foodsaver_id): bool
	{
		/*
		 * only send email if the user is not online
		 */
		if (!$this->mem->userIsActive($foodsaver_id) && $this->mem->get('infomail_message_' . $foodsaver_id)) {
			return true;
		}

		return true;
	}

	/**
	 * Method returns an array of all conversation from the user.
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array
	 */
	public function listConversations(int $limit = -1, int $offset = 0)
	{
		$paginate = '';
		if ($limit !== -1) {
			$paginate = ' LIMIT ' . (int)$offset . ',' . (int)$limit;
		}

		if ($conversations = $this->q('
			SELECT
				c.`id`,
				c.`last`,
				UNIX_TIMESTAMP(c.`last`) AS last_ts,
				c.`member`,
				c.`last_message`,
				c.`last_foodsaver_id`,
				hc.unread,
				c.name

			FROM
				fs_conversation c,
				`fs_foodsaver_has_conversation` hc

			WHERE
				hc.conversation_id = c.id

			AND
				hc.foodsaver_id = ' . (int)$this->session->id() . '
				
			AND
			    c.last_message <> ""

			ORDER BY
				hc.unread DESC,
				c.`last` DESC
			' . $paginate . '
		')
		) {
			foreach ($conversations as $i => $iValue) {
				$member = @unserialize($conversations[$i]['member']);
				// unserialize error handling
				if ($member === false) {
					$this->updateDenormalizedConversationData($conversations[$i]['id']);
				}
				$conversations[$i]['member'] = $member;
			}

			return $conversations;
		}

		return [];
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

	public function chatHistory($conversation_id)
	{
		if ($conversation_id > 0) {
			return $this->q('
				SELECT
					fs.name AS n,
					m.`body` AS m,
					UNIX_TIMESTAMP(m.`time`) AS t,
					fs.photo AS p

				FROM
					`fs_msg` m,
					`fs_foodsaver` fs

				WHERE
					m.foodsaver_id = fs.id

				AND
					m.conversation_id = ' . (int)$conversation_id . '

				ORDER BY
					m.`time` DESC

				LIMIT 0,50
			');
		}
	}

	public function loadMore(int $conversation_id, int $last_message_id, int $limit = 20): array
	{
		return $this->q('
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
				m.conversation_id = ' . $conversation_id . '

			AND
				m.id < ' . $last_message_id . '

			ORDER BY
				m.`time` DESC

			LIMIT 0,' . $limit . '
		');
	}

	public function getLastMessages($conv_id, $last_msg_id): array
	{
		return $this->q('
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
				m.conversation_id = ' . (int)$conv_id . '

			AND
				m.id > ' . (int)$last_msg_id . '

			ORDER BY
				m.`time` ASC
		');
	}

	/**
	 * set conversations as read.
	 *
	 * @param array $conv_ids
	 *
	 * @return bool | int
	 */
	public function setAsRead($conv_ids)
	{
		$this->mem->userDel($this->session->id(), 'msg-update');

		return $this->update('UPDATE fs_foodsaver_has_conversation SET unread = 0 WHERE foodsaver_id = ' . (int)$this->session->id() . ' AND conversation_id IN(' . implode(',', $conv_ids) . ')');
	}

	public function listConversationUpdates($conv_ids)
	{
		if ($return = $this->q('
			SELECT
				`id` AS id,
				`last` AS time,
				`last_message` AS body,
				`member`

			FROM
				`fs_conversation`

			WHERE
				`id` IN(' . implode(',', $conv_ids) . ')
		')
		) {
			foreach ($return as $i => $iValue) {
				$return[$i]['member'] = unserialize($return[$i]['member']);
			}

			return $return;
		}

		return false;
	}

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

	public function mayConversation($conversation_id): bool
	{
		if ($this->q('SELECT foodsaver_id FROM `fs_foodsaver_has_conversation` WHERE `foodsaver_id` = ' . (int)$this->session->id() . ' AND conversation_id = ' . (int)$conversation_id)) {
			return true;
		}

		return false;
	}

	private function updateDenormalizedConversationData($cids = false): void
	{
		if ($cids === false) {
			$cids = $this->qCol('SELECT id FROM fs_conversation');
		} elseif (!is_array($cids)) {
			$cids = array($cids);
		}
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
		}
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

	public function deleteUserFromConversation($cid, $fsid, $deleteAlways = false): bool
	{
		/*
		 * delete only users from non 1:1 conversations
		 */
		if ($deleteAlways || ((int)$this->qOne('SELECT COUNT(foodsaver_id) FROM `fs_foodsaver_has_conversation` WHERE conversation_id = ' . (int)$cid) > 2)) {
			$this->del('DELETE FROM `fs_foodsaver_has_conversation` WHERE conversation_id = ' . (int)$cid . ' AND foodsaver_id = ' . (int)$fsid);
			$this->updateDenormalizedConversationData($cid);
		}

		return false;
	}

	public function insertConversation($recipients, $locked = false, $unread = true)
	{
		/*
		 * first get one new conversation
		 */
		$lock = 0;
		if ($locked) {
			$lock = 1;
		}

		$ur = $unread ? 1 : 0;

		$sql = 'INSERT INTO `fs_conversation`
			(
				`start`,
				`last`,
				`last_foodsaver_id`,
				`start_foodsaver_id`,
				`locked`
			)
			VALUES (NOW(),NOW(),' . (int)$this->session->id() . ',' . (int)$this->session->id() . ',' . (int)$lock . ')';

		if (($cid = $this->insert($sql)) > 0) {
			/*
			 * last add all recipients to this conversation
			 */
			$values = array();
			unset($recipients[(int)$this->session->id()]);
			foreach ($recipients as $r) {
				$values[] = '(' . (int)$r . ',' . (int)$cid . ',' . $ur . ')';
			}

			// add current user extra to set unread = 0
			$values[] = '(' . (int)$this->session->id() . ',' . (int)$cid . ',0)';

			$this->insert('
				INSERT INTO
					`fs_foodsaver_has_conversation` (`foodsaver_id`, `conversation_id`, `unread`)

				VALUES
					' . implode(',', $values) . '
			');

			$this->updateDenormalizedConversationData($cid);

			return $cid;
		}
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
