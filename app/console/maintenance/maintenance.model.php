<?php
class MaintenanceModel extends ConsoleModel
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function updateBezirkIds()
	{
		$foodsaver = $this->q('SELECT `bezirk_id`, `id` FROM `'.PREFIX.'foodsaver` WHERE `bezirk_id` != 0');
	
		$query = array();
	
		foreach ($foodsaver as $fs)
		{
			$query[] = '('.(int)$fs['id'].','.(int)$fs['bezirk_id'].',1)';
		}
	
		$this->sql('
			REPLACE INTO `'.PREFIX.'foodsaver_has_bezirk`
			(
				`foodsaver_id`,
				`bezirk_id`,
				`active`
			)
			VALUES
			'.implode(',', $query).'
		');
	}
	
	public function deleteUnconformedFetchDates()
	{
		return $this->del('DELETE FROM '.PREFIX.'abholer WHERE confirmed = 0 AND `date` < NOW()');
	}
	
	public function listAvatars()
	{
		return $this->q('SELECT id, photo FROM '.PREFIX.'foodsaver WHERE photo != ""');
	}
	
	public function noAvatars($foodsaver_ids)
	{
		return $this->update('UPDATE '.PREFIX.'foodsaver SET photo = "" WHERE id IN('.implode(',',$foodsaver_ids).')');
	}
	
	public function getUserInfo()
	{
		return $this->q('SELECT id, infomail_message FROM '.PREFIX.'foodsaver');
	}
	
	public function listOldBellIds($days = 7)
	{
		return $this->qCol('
			SELECT id
			FROM `'.PREFIX.'bell`
			WHERE `time` <= NOW( ) - INTERVAL '.(int)$days.' DAY
		');
	}
	
	public function deactivateOldBaskets($days = 14)
	{
		return $this->update('
			UPDATE '.PREFIX.'basket
			SET `status` = 6 WHERE
			DATEDIFF(NOW(), `time`) > '.(int)$days.'
			AND `status` = 1
		');
	}
	
	public function deleteBells($bell_ids)
	{
		$this->del('
			DELETE FROM '.PREFIX.'foodsaver_has_bell 
			WHERE 	bell_id IN('.implode(',', $bell_ids).')
		');
		
		$this->del('
			DELETE FROM `'.PREFIX.'bell` 
			WHERE 	id IN('.implode(',', $bell_ids).')
		');
		
		$this->sql('LOCK TABLES `'.PREFIX.'bell` WRITE');
		$this->sql('ALTER TABLE `'.PREFIX.'bell` AUTO_INCREMENT = (SELECT MAX(id) FROM `'.PREFIX.'bell`)');
		$this->sql('UNLOCK TABLES');
	}
	
	/*
	public function getBotschafterIds()
	{
		return $this->qCol('
				SELECT DISTINCT bot.foodsaver_id AS id
	
				FROM
				    `'.PREFIX.'botschafter` bot,
				    `'.PREFIX.'bezirk` b
	
				WHERE
				    bot.bezirk_id = b.id
	
				AND
				    b.`type` != 7
			');
	}
	
	public function updateRolle($fsids,$rolle_id)
	{
		return $this->update('
			UPDATE `'.PREFIX.'foodsaver`
	
			SET
				`rolle` = '.(int)$rolle_id.'
	
			WHERE
				`rolle` != '.(int)$rolle_id.'
	
			AND
				`id` IN('.implode(',', $botsch).')
		');
	}
	*/
	
	public function updateRolle()
	{
	
		if($botschafter = $this->q('SELECT DISTINCT foodsaver_id FROM `'.PREFIX.'botschafter` '))
		{
			$foodsaver = $this->q('
				SELECT DISTINCT bot.foodsaver_id
	
				FROM
				    `'.PREFIX.'botschafter` bot,
				    `'.PREFIX.'bezirk` b
	
				WHERE
				    bot.bezirk_id = b.id
	
				AND
				    b.`type` != 7
			
			');
			$botsch = array();
	
			foreach ($botschafter as $b)
			{
				$botsch[$b['foodsaver_id']] = $b['foodsaver_id'];
			}
	
			if(!empty($botsch))
			{
				$count = $this->update('
					UPDATE `'.PREFIX.'foodsaver`
	
					SET
						`rolle` = '.rolleWrap('bot').'
	
					WHERE
						`rolle` < '.rolleWrap('bot').'
	
					AND
						`id` IN('.implode(',', $botsch).')
				');
				info($count.' botsch');
			}
	
			$nomore = array();
			foreach ($foodsaver as $fs)
			{
				if(!isset($botsch[$fs['foodsaver_id']]))
				{
					$nomore[] = $fs['foodsaver_id'];
				}
			}
			if(!empty($nomore))
			{
				$count = $this->update('
					UPDATE `'.PREFIX.'foodsaver` SET `rolle` = '.rolleWrap('fs').' WHERE `id` IN('.implode(',', $nomore).')
				');
				info($count.' nomore');
			}
		}
	}
}