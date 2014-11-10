<?php
class Model extends ManualDb
{
	public function addPushQueue($sender_id,$recip_id,$title,$message,$ids = false,$data = array('t'=>0),$message_id = 0, $status = 0)
	{
		if($ids === false)
		{
			$ids = $this->getValues(array('gcm','iosid'), 'foodsaver', $recip_id);
		}
		
		$data['i'] = (int)fsId();
		
		if($ids['gcm'] != '' || $ids['iosid'] != '')
		{
			$this->insert('
				INSERT INTO `fs_pushqueue`
				(
					`sender_id`, 
					`recip_id`, 
					`time`, 
					`message_id`, 
					`title`, 
					`message`, 
					`data`, 
					`status`,
					`id_gcm`,
					`id_apn`
				) 
				VALUES 
				(
					'.(int)$sender_id.',
					'.(int)$recip_id.',
					NOW(),
					'.(int)$message_id.',
					'.$this->strval($title).',
					'.$this->strval($message).',
					'.$this->strval(serialize($data)).',
					'.(int)$status.',
					'.$this->strval($ids['gcm']).',
					'.$this->strval($ids['iosid']).'
				)		
			');
		}
	}
	
	public function mayBezirk($bid)
	{
		if(isset($_SESSION['client']['bezirke'][$bid]) || isBotschafter() || isOrgaTeam())
		{
			return true;
		}
		return false;
	}
	
	public function isBotschafter($fsid)
	{
		if($this->q('SELECT foodsaver_id FROM '.PREFIX.'botschafter WHERE foodsaver_id = '.(int)$fsid.' LIMIT 1'))
		{
			return true;
		}
		return false;
	}
	
	public function getContent($id)
	{
		if($cnt = $this->qRow('
			SELECT 	`title`,`body` FROM '.PREFIX.'content WHERE `id` = '.(int)$id.'		
		'))
		{
			return $cnt;
		}
		return false;
	}
	
	public function getBezirke()
	{
		if(isset($_SESSION['client']['bezirke']) && is_array($_SESSION['client']['bezirke']))
		{
			return $_SESSION['client']['bezirke'];
		}
	}
	
	public function buddyStatus($fsid)
	{
		if(($status = $this->qOne('SELECT `confirmed` FROM '.PREFIX.'buddy WHERE `foodsaver_id` = '.(int)fsId().' AND `buddy_id` = '.(int)$fsid.'')) !== false)
		{
			return $status;
		}
		
		return -1;
	}
	
	public function buddyRequest($fsid)
	{
		$this->insert('
			REPLACE INTO `'.PREFIX.'buddy`(`foodsaver_id`, `buddy_id`, `confirmed`)
			VALUES ('.(int)fsId().','.(int)$fsid.',0)
		');
		return true;
	}
	
	public function confirmBuddy($fsid)
	{
		$this->insert('
			REPLACE INTO `'.PREFIX.'buddy`(`foodsaver_id`, `buddy_id`, `confirmed`)
			VALUES ('.(int)fsId().','.(int)$fsid.',1)
		');
		$this->insert('
			REPLACE INTO `'.PREFIX.'buddy`(`foodsaver_id`, `buddy_id`, `confirmed`)
			VALUES ('.(int)$fsid.','.(int)fsid().',1)
		');
	}
	
	public function removeBuddy($fsid)
	{
		$this->del('
			DELETE FROM `'.PREFIX.'buddy`
			WHERE 	`foodsaver_id` = '.(int)fsId().'
			AND 	`buddy_id` = '.$fsid.'
		');
		$this->del('
			DELETE FROM `'.PREFIX.'buddy`
			WHERE 	`foodsaver_id` = '.(int)$fsid.'
			AND 	`buddy_id` = '.fsId().'
		');
	}
	
	public function getLocation($id)
	{
		return $this->qRow('
			SELECT id, name, lat, lon, zip, city, street
			FROM  '.PREFIX.'location
			WHERE 	id = '.$this->intval($id).'
		');
	}
	
	public function getLocationByLatLon($lat,$lon)
	{
		return $this->qRow('
			SELECT (id, name, lat, lon, zip, city, street)
			FROM  '.PREFIX.'location
			WHERE 	lat = '.$this->floatval($lat).'
			AND 	lon = '.$this->floatval($lon).'
		');
	}
	
	public function getLocationIdByLatLon($lat,$lon)
	{
		$lat = round($lat,8);
		$lon = round($lon,8);
		return $this->qOne('
			SELECT (id)
			FROM  '.PREFIX.'location
			WHERE 	lat = '.$this->floatval($lat).'
			AND 	lon = '.$this->floatval($lon).'
		');
	}
	
	public function addLocation($location_name, $lat, $lon, $anschrift, $plz, $ort)
 	{
 		$lat = round($lat,8);
 		$lon = round($lon,8);
 		return $this->insert('	
 			INSERT INTO '.PREFIX.'location (name, lat, lon, zip, city, street) 
 			VALUES
 			(		
 				'.$this->strval($location_name).',
 				'.$this->floatval($lat).',
 				'.$this->floatval($lon).',
 				'.$this->strval($plz).',
 				'.$this->strval($ort).',
 				'.$this->strval($anschrift).'
 			)
 		');
 	}
 	
 	public function getCache($id)
 	{
 		if($value = $this->qOne('
 			SELECT `value` FROM `fs_cache` 
 			WHERE `id` = '.$this->strval($id).'
 		'))
 		{
 			return unserialize($value);
 		}
 		
 		return false;
 	}
 	
 	public function replaceCache($id,$value)
 	{
 		$value = serialize($value);
 		return $this->insert('
 				REPLACE INTO `fs_cache`
 				(`id`,`value`)
 				VALUES('.$this->strval($id).','.$this->strval($value).')
 		');
 	}
 	
 	public function setCache($id,$value)
 	{
 		$value = serialize($value);
 		return $this->insert('
 			INSERT INTO `fs_cache`
 			(`id`,`value`)
 			VALUES('.$this->strval($id).','.$this->strval($value).')
 			
 		');
 	}
 	
 	public function delBells($identifier)
 	{
 		if($bells = $this->q('SELECT id FROM '.PREFIX.'bell WHERE identifier = '.$this->strval($identifier)))
 		{
 			$ids = array();
 			foreach ($bells as $b)
 			{
 				$ids[(int)$b['id']] = (int)$b['id'];
 			}
 			
 			$ids = implode(',',$ids);
 			
 			$this->del('DELETE FROM '.PREFIX.'foodsaver_has_bell WHERE bell_id IN('.$ids.')');
 			$this->del('DELETE FROM '.PREFIX.'bell WHERE id IN('.$ids.')');
 		}
 	}
 	
 	public function addBell($foodsaver_ids, $title, $body, $icon, $link_attributes, $vars, $identifier = '',$closeable = 1)
 	{
 		
 		if(!is_array($foodsaver_ids))
 		{
 			$foodsaver_ids = array($foodsaver_ids);
 		}
 		
 		if($link_attributes !== false)
 		{
 			$link_attributes = serialize($link_attributes);
 		}
 		
 		if($vars !== false)
 		{
 			$vars = serialize($vars);
 		}
 		
 		if($bid = $this->insert('INSERT INTO `'.PREFIX.'bell`(`name`, `body`, `vars`, `attr`, `icon`, `identifier`,`time`,`closeable`) VALUES ('.$this->strval($title).','.$this->strval($body).','.$this->strval($vars).','.$this->strval($link_attributes).','.$this->strval($icon).','.$this->strval($identifier).',NOW(),'.(int)$closeable.')'))
 		{
 			$values = array();
 			foreach ($foodsaver_ids as $id)
 			{
 				if(is_array($id))
 				{
 					$id = $id['id'];
 				}
 				
 				$values[] = '('.(int)$id.','.(int)$bid.',0)';
 			}
 			
 			return $this->insert('INSERT INTO `'.PREFIX.'foodsaver_has_bell`(`foodsaver_id`, `bell_id`, `seen`) VALUES '.implode(',', $values));
 		}
 		
 		return false;
 	}
 	
 	/**
 	 * Method to check users online status by checking timestamp from memcahce
 	 *
 	 * @param integer $fs_id
 	 * @return boolean
 	 */
 	public function isActive($fs_id)
 	{
 		if($time = Mem::get('activity_'.$fs_id))
 		{
 			if((time()-$time) > 600)
 			{
 				return false;
 			}
 			else
 			{
 				return true;
 			}
 		}
 	
 		/*
 			if($time = $this->qOne('SELECT UNIX_TIMESTAMP(`zeit`) FROM `'.PREFIX.'activity` WHERE `foodsaver_id` = '.$this->intval($fs_id)))
 			{
 		if((time()-$time) > 600)
 		{
 		return false;
 		}
 		else
 		{
 		return true;
 		}
 		}
 		*/
 		return false;
 	}
 	
 	public function updateSleepMode($status,$from,$to,$msg)
 	{
 		return $this->update('
 			UPDATE 
 				'.PREFIX.'foodsaver 
 				
 			SET	
 				`sleep_status` = '.(int)$status.',
 				`sleep_from` = '.$this->dateval($from).',
 				`sleep_until` = '.$this->dateval($to).',
 				`sleep_msg` = '.$this->strval($msg).'

 			WHERE 
 				id = '.(int)fsId().'
 		');
 	}
 	
 	public function message($recip_id, $foodsaver_id, $message, $unread = 1)
 	{
 		$model = loadModel('msg');
 		
 		$recd = 0;
 		if($unread == 0)
 		{
 			$recd = 1;
 		}
 		else
 		{
 			$unread = 1;
 		}
 		
 		$this->addPushQueue(fsId(), $recip_id, S::user('name').' hat Dir eine Nachricht geschrieben', $message,false,array('t'=>0,'i'=>fsId(),'t'=>time()));
 		
 		if($conversation_id = $model->user2conv($recip_id))
		{
			return $model->sendMessage($conversation_id,$message);
		}
		return false;
 	}
}