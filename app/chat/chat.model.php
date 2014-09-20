<?php
class ChatModel extends Model
{
	public function getUser($id)
	{
		return $this->qRow('
			SELECT 	`name` AS n,
					id AS f,
					photo AS p

			FROM 	'.PREFIX.'foodsaver
				
			WHERE 	id = '.$id.'
		');
	}
	
	function getUser2($id)
	{
		return $this->qRow('SELECT id, name, photo, gcm, iosid FROM fs_foodsaver WHERE id = '.(int)$id);
	
	}
	
	public function setRead($id)
	{
		return $this->update('
			UPDATE 	'.PREFIX.'message
			SET 	unread = 0
			WHERE 	recip_id = '.(int)fsId().'
			AND 	sender_id = '.(int)$id.'
			AND 	unread = 1		
		');
	}
	
	
	
	public function getLasMsg($id)
	{
		$sql = "SELECT 	fs.id AS `f`,
					fs.name AS `n`,
					c.msg AS `m`,
					c.time AS sent,
					fs.photo AS p
			from 	fs_message c,
					fs_foodsaver fs
			WHERE c.sender_id = fs.id
			AND
			(
				(	
					c.sender_id = ".(int)$id."
					AND 
					c.recip_id = ".(int)fsId()."
				)
				OR 
				(
					c.sender_id = ".(int)fsId()."
					AND 
					c.recip_id = ".(int)$id."
				)
			)
			
			ORDER BY sent DESC
			LIMIT 10";
		$out = array();
		if($ret = $this->q($sql))
		{
			return array_reverse($ret);
		}
		
		return false;
	}
}