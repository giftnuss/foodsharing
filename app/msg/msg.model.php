<?php
class MsgModel extends Model
{
	public function listPeople()
	{
		return $this->q('
			SELECT id, name AS value FROM '.PREFIX.'foodsaver LIMIT 10		
		');
	}
	
	public function user2conv($fsid)
	{
		return $this->addConversation(array($fsid=> $fsid),false);
	}
	
	/**
	 * Adds a new Conversation but first check if is there allready an conversation with exaclty this user_ids
	 * 
	 * @param array $recips
	 * @param string $body
	 */
	public function addConversation($recips,$body = false)
	{
		/*
		 * add the current user to the recipients
		 */
		$recips[(int)fsId()] = (int)fsId();
		
		/*
		 * make sure the order of this array
		 */
		ksort($recips);
		
		$conversation_id = false;
		
		/*
		 * First we want to check is there allready an conversation with exacly those user_ids stored in $recips array
		*/
		if($conv = $this->qRow('
			SELECT
				conversation_id,
				GROUP_CONCAT(foodsaver_id ORDER BY foodsaver_id SEPARATOR ":") AS idstring
		
			FROM
				fs_foodsaver_has_conversation
		
			GROUP BY
				conversation_id
		
			HAVING
				
				idstring = "' . implode(':',$recips).'"'))
		{
			$conversation_id = $conv['conversation_id'];
		}
		
		/*
		 * If we dont have an existing conversation create a new one
		*/
		if(!$conversation_id)
		{
			$conversation_id = $this->insertConversation($recips);
		}
		
		$mid = 0;
		if($body !== false)
		{
			$mid = $this->insert('
				INSERT INTO `'.PREFIX.'msg`(`conversation_id`, `foodsaver_id`, `body`, `time`)
				VALUES ('.(int)$conversation_id.','.(int)fsId().','.$this->strval($body).',NOW())
			');
		}
		else
		{
			$body = '';
		}
		
		$member = $this->listConversationMembers($conversation_id);
		
		/*
		 * UPDATE conversation
		*/
		$this->update('
			UPDATE
				`'.PREFIX.'conversation`
			
			SET
				`last` = NOW(),
				`last_foodsaver_id` = '.(int)fsId().',
				`last_message` = '.$this->strval($body).',
				`last_message_id` = '.(int)$mid.',
				`member` = '.$this->strval(serialize($member)).'
			
			WHERE
			`id` = '.(int)$conversation_id.'
		');
		
		return $conversation_id;
	}
	
	/**
	 * Renames an Conversation
	 */
	public function renameConversation($cid,$name)
	{
		return $this->update('UPDATE '.PREFIX.'conversation SET name = '.$this->strval($name).' WHERE id = '.(int)$cid);
	}

  public function conversationLocked($cid)
  {
    $res = $this->qOne('SELECT locked FROM '.PREFIX.'conversation WHERE id = '.(int)$cid);
    return $res;
  }
	
	public function updateConversation($cid,$last_fs_id,$body,$last_message_id)
	{
		return $this->update('
				UPDATE
					`'.PREFIX.'conversation`
				
				SET
					`last` = NOW(),
					`last_foodsaver_id` = '.(int)$last_fs_id.',
					`last_message` = '.$this->strval($body).',
					`last_message_id` = '.(int)$last_message_id.'
				
				WHERE
					`id` = '.(int)$cid.'
		');
	}
	
	public function findConnectedPeople($term)
	{
		$out = array();
		
		// add user in bezirk and groups
		
		if(isset($_SESSION['client']['bezirke']) && is_array($_SESSION['client']['bezirke']) && count($_SESSION['client']['bezirke'] > 0))
		{
			$ids = array();
			foreach ($_SESSION['client']['bezirke'] as $i => $bezirk)
			{
				$ids[] = $bezirk['id'];
			}
			
			$sql = '
				SELECT 
					DISTINCT fs.id AS id,
					CONCAT(fs.name," ",fs.nachname ) AS value
					
				FROM
					'.PREFIX.'foodsaver fs,
					'.PREFIX.'foodsaver_has_bezirk hb
					
				WHERE 
					hb.foodsaver_id = fs.id 
					
				AND 
					hb.bezirk_id IN('.implode(',', $ids).') 
					
				AND 
					CONCAT(fs.name," ",fs.nachname ) LIKE "%'.$this->safe($term).'%"
			';
			
			
			
			if($user = $this->q($sql))
			{
				$out = array_merge($out,$user);	
			}
		}
		
		return $out;
	}
	
	public function getLastConversationId()
	{
		if($res = $this->qRow('SELECT MAX( `time` ) , conversation_id FROM '.PREFIX.'msg'))
		{
			return $res['conversation_id'];
		}
		
		return false;
	}
	
	public function getOldConvStartDate($sender_id,$recip_id)
	{
		
	}
	
	public function listConversationMembers($conversation_id)
	{
		return $this->q('
			SELECT 
				fs.id,
				fs.name,
				fs.photo,
				fs.email,
				fs.geschlecht,
				fs.gcm,
				fs.iosid

			FROM 
				`'.PREFIX.'foodsaver_has_conversation` hc,
				`'.PREFIX.'foodsaver` fs
				
			WHERE
				hc.foodsaver_id = fs.id 
				
			AND
				hc.conversation_id = '.(int)$conversation_id.'
		');
	}
	
	public function wantMsgEmailInfo($foodsaver_id)
	{
		/*
		 * only send email if the user is not online
		 */
		if(!$this->isActive($foodsaver_id))
		{
			if(Mem::get('infomail_message_'.$foodsaver_id))
			{
				return true;
			}
		}
		
		return true;
	}
	
	/**
	 * Method to get or check if ther are unread messages to one conversation
	 */
	public function getUnreadMessages($cid)
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
				m.conversation_id = '.(int)$cid.'
				
			AND 
				
				
			ORDER BY 
				m.`time` ASC
		');
	}
	
	/**
	 * Method returns an array of all conversation from the user
	 * 
	 * @return Ambigous <boolean, array >
	 */
	public function listConversations($limit = '')
	{
		if($limit != '')
		{
			$limit = ' LIMIT 0,'.(int)$limit;
		}
		
		if($convs = $this->q('
			SELECT 
				c.`id`,
				c.`last`,
				UNIX_TIMESTAMP(c.`last`) AS last_ts,
				c.`member`,
				c.`last_message`,
				hc.unread,
				c.name
				
			FROM 
				'.PREFIX.'conversation c,
				`'.PREFIX.'foodsaver_has_conversation` hc
				
			WHERE 
				hc.conversation_id = c.id
				
			AND 
				hc.foodsaver_id = '.(int)fsId().'
				
			AND
				c.last_message_id != 0
				
			ORDER BY c.`last` DESC
			'.$limit.'		
		'))
		{
			
			for($i=0;$i<count($convs);$i++)
			{
				$member = @unserialize($convs[$i]['member']);
				// unserialize error handling
				if($member === false){
					$member = $this->listConversationMembers($convs[$i]['id']);
					$this->update('
						UPDATE
							`'.PREFIX.'conversation`
					
						SET
							`member` = '.$this->strval(serialize($member)).'
					
						WHERE
							`id` = '.(int)$convs[$i]['id'].'
					');
				}
				$convs[$i]['member'] = $member;
			}
			
			return $convs;
		}
		return false;
	}
	
	/**
	 * check if there are unread messages in conversation give back the conversation ids
	 * 
	 * @return Ambigous <boolean, array >
	 */
	public function checkConversationUpdates()
	{
		/*
		 * for more speed check the memcache first
		 */
		
		/*
		 * Memcache var is settet but no updates
		 */
		$cache = Mem::user(fsId(),'msg-update');
		
		if($cache === 0)
		{
			return false;
		}
		else if(is_array($cache))
		{
			Mem::userSet(fsId(), 'msg-update', 0);
			return $cache;
		}
		
		/*
		 * Memcache is not settedso get coonversation ids direct fromdm
		 */
		else
		{
			Mem::userSet(fsId(), 'msg-update', 0);
			return $this->getUpdatedConversationIds();
		}
	}
	
	public function getUpdatedConversationIds()
	{
		return $this->qCol('SELECT conversation_id FROM '.PREFIX.'foodsaver_has_conversation WHERE foodsaver_id = '.(int)fsId().' AND unread = 1');
	}
	
	public function checkChatUpdates($ids)
	{
		return $this->qColKey('SELECT conversation_id FROM '.PREFIX.'foodsaver_has_conversation WHERE foodsaver_id = '.(int)fsId().' AND unread = 1 AND conversation_id IN('.implode(',', $ids).')');
	}
	
	public function chatHistory($fsid)
	{
		if($conversation_id = $this->user2conv($fsid))
		{
			return $this->q('
				SELECT
					fs.name AS n,
					m.`body` AS m,
					UNIX_TIMESTAMP(m.`time`) AS t,
					fs.photo AS p
			
				FROM
					`'.PREFIX.'msg` m,
					`'.PREFIX.'foodsaver` fs
			
				WHERE
					m.foodsaver_id = fs.id
			
				AND
					m.conversation_id = '.(int)$conversation_id.'
				
				ORDER BY
					m.`time` DESC
				
				LIMIT 0,20
			');
		}
		
	}
	
	public function getLastMessages($conv_id,$last_msg_id)
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
				m.conversation_id = '.(int)$conv_id.'
				
			AND 
				m.id > '.(int)$last_msg_id.'
				
			ORDER BY 
				m.`time` ASC
		');
	}
	
	/**
	 * set conversatioens as readed
	 * @param array $conv_ids
	 * @return boolean | int
	 */
	public function setAsRead($conv_ids)
	{
		Mem::userDel(fsId(), 'msg-update');
		
		return $this->update('UPDATE '.PREFIX.'foodsaver_has_conversation SET unread = 0 WHERE foodsaver_id = '.(int)fsId().' AND conversation_id IN('.implode(',', $conv_ids).')');
	}
	
	public function listConversationUpdates($conv_ids)
	{
		if($return = $this->q('
			SELECT 
				`id` AS id,
				`last` AS time,
				`last_message` AS body,
				`member`

			FROM
				`'.PREFIX.'conversation`
				
			WHERE
				`id` IN(' . implode(',', $conv_ids) . ')
		'))
		{
			for($i=0;$i<count($return);$i++)
			{
				$return[$i]['member'] = unserialize($return[$i]['member']);
			}
			
			return $return;
		}
		
		return false;
	}
	
	public function sendMessage($cid,$body)
	{
		if($mid = $this->insert('INSERT INTO `'.PREFIX.'msg`(`conversation_id`, `foodsaver_id`, `body`, `time`) VALUES ('.(int)$cid.','.(int)fsId().','.$this->strval($body).',NOW())'))
		{
			$this->update('UPDATE `'.PREFIX.'foodsaver_has_conversation` SET unread = 1 WHERE conversation_id = '.(int)$cid.' AND `foodsaver_id` != '.(int)fsId());
			$this->updateConversation($cid, fsId(), $body, $mid);
			return $mid;
		}
		return false;
	}
	
	public function loadConversationMessages($conversation_id)
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
				`'.PREFIX.'msg` m,
				`'.PREFIX.'foodsaver` fs
			
			WHERE 
				m.foodsaver_id = fs.id
			
			AND 
				m.conversation_id = '.(int)$conversation_id.'
				
			ORDER BY 
				m.`time` DESC
				
			LIMIT 0,20
		');
	}
	
	public function mayConversation($conversation_id)
	{
		if($this->q('SELECT foodsaver_id FROM `'.PREFIX.'foodsaver_has_conversation` WHERE `foodsaver_id` = '.(int)fsId().' AND conversation_id = '.(int)$conversation_id))
		{
			return true;
		}
		return false;
	}
	
	public function fixchats()
	{
		if($chats = $this->qCol('SELECT id FROM fs_conversation'))
		{
			foreach ($chats as $id)
			{
				
				$member = $this->listConversationMembers($id);
				
				/*
				 * UPDATE conversation
				*/
				$this->update('
				UPDATE
					`'.PREFIX.'conversation`
			
				SET
					`member` = '.$this->strval(serialize($member)).'
			
				WHERE
					`id` = '.(int)$id.'
			');
			}
		}
	}
	
	public function deleteUserFromConversation($cid,$fsid)
	{
		/**
		 * delete only users from non 1:1 conversations
		 */
		if((int)$this->qOne('SELECT COUNT(foodsaver_id) FROM `'.PREFIX.'foodsaver_has_conversation` WHERE conversation_id = '.(int)$cid) > 2)
		{
			$this->del('DELETE FROM `'.PREFIX.'foodsaver_has_conversation` WHERE conversation_id = '.(int)$cid.' AND foodsaver_id = '.(int)$fsid);
			if($member = $this->qOne('SELECT member FROM '.PREFIX.'conversation WHERE id = '.(int)$cid))
			{
				$member = unserialize($member);
				$out = array();
				
				foreach ($member as $k => $v)
				{
					if($v['id'] != fsId())
					{
						$out[$k] = $v;
					}
				}

				return $this->update('UPDATE '.PREFIX.'conversation SET member = '.$this->strval(serialize($out)).' WHERE id = '.(int)$cid);
			}
			
			return false;
		}
	}
	
	public function insertConversation($recipients)
	{
		/*
		 * first get one new conversation
		 */
		
		$sql = 'INSERT INTO `'.PREFIX.'conversation`
			(
				`start`, 
				`last`, 
				`last_foodsaver_id`,
				`start_foodsaver_id`
			) 
			VALUES (NOW(),NOW(),'.(int)fsId().','.(int)fsId().')';
		
		if(($cid = $this->insert($sql)) > 0)
		{
			/*
			 * last add all recipients to this conversation
			 */
			$values = array();
			unset($recipients[(int)fsId()]);
			foreach ($recipients as $r)
			{
				$values[] = '('.(int)$r.','.(int)$cid.',1)';
			}
			
			// add current user extra to set unread = 0
			$values[] = '('.(int)fsId().','.(int)$cid.',0)';
			
			$this->insert('
				INSERT INTO 
					`'.PREFIX.'foodsaver_has_conversation` (`foodsaver_id`, `conversation_id`, `unread`) 
					
				VALUES
					'.implode(',', $values).' 		
			');
			
			return $cid;
		}
	}
}
