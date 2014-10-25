<?php
class ProfileModel extends Model
{
	private $fs_id;
	
	public function setFsId($id)
	{
		$this->fs_id = $this->intval($id);
	}
	
	public function rate($fsid,$rate,$type = 1,$message = '')
	{
		return $this->insert('
			REPLACE INTO `'.PREFIX.'rating`
			(
				`foodsaver_id`,
				`rater_id`,
				`rating`,
				`ratingtype`,
				`msg`,
				`time`
			)		
			VALUES
			(
				'.(int)$fsid.',
				'.(int)fsId().',
				'.(int)$rate.',
				'.(int)$type.',
				'.$this->strval($message).',
				NOW()
			)
		');
	}
	
	public function getRateMessage($fsid)
	{
		return $this->qOne('
			SELECT 	`msg` 
			FROM	`'.PREFIX.'rating`
			WHERE 	`foodsaver_id` = '.(int)$fsid.'
			AND 	`rater_id` = '.(int)fsId().'
		');
	}
	
	public function getData()
	{
		$data = $this->qRow('
		
			SELECT 	fs.`id`,
					fs.`autokennzeichen_id`,
					fs.`fs_id`,
					fs.`bezirk_id`,
					fs.`plz`,
					fs.`stadt`,
					fs.`lat`,
					fs.`lon`,
					fs.`email`,
					fs.`name`,
					fs.`nachname`,
					fs.`anschrift`,
					fs.`telefon`,
					fs.`handy`,
					fs.`geschlecht`,
					fs.`geb_datum`,
					fs.`anmeldedatum`,
					fs.`photo`,
					fs.`photo_public`,
					fs.`about_me_public`,
					fs.`orgateam`,
					fs.`data`,
					fs.stat_fetchweight,
					fs.stat_fetchcount,
					fs.stat_ratecount,
					fs.stat_rating,
					fs.stat_postcount,
					fs.stat_buddycount,
					fs.stat_fetchrate,
					fs.stat_bananacount,
					fs.verified,
					fs.anmeldedatum,
					fs.sleep_status,
					fs.sleep_msg,
					UNIX_TIMESTAMP(fs.sleep_from) AS sleep_from_ts,
					UNIX_TIMESTAMP(fs.sleep_until) AS sleep_until_ts
		
			FROM 	'.PREFIX.'foodsaver fs
		
			WHERE 	fs.id = '.(int)$this->fs_id.'
		
		');
		//echo 'SELECT COUNT(rater_id) FROM `fs_rating` WHERE rater_id = '.(int)fsId().' AND foodsaver_id = '.(int)$this->fs_id.' AND ratingtype = 2';
		$data['bouched'] = false;
		$data['bananen'] = false;
		if($this->qOne('SELECT 1 FROM `fs_rating` WHERE rater_id = '.(int)fsId().' AND foodsaver_id = '.(int)$this->fs_id.' AND ratingtype = 2'))
		{
			$data['bouched'] = true;
		}
		
		$data['bananen'] = $this->q('
				SELECT 	fs.id,
						fs.name,
						fs.photo,
						r.`msg`,
						r.`time`,
						UNIX_TIMESTAMP(r.`time`) AS time_ts
		
				FROM 	`'.PREFIX.'foodsaver` fs,
						 `'.PREFIX.'rating` r
				WHERE 	r.rater_id = fs.id
				AND 	r.foodsaver_id = '.(int)$this->fs_id.'
				AND 	r.ratingtype = 2
		');
		
		if(!$data['bananen'])
		{
			$data['bananen'] = array();
		}
		
		//echo((int)$data['bananen']);echo'<<<';die();
		
		$this->update('UPDATE '.PREFIX.'foodsaver SET stat_bananacount = '.(int)count($data['bananen']).' WHERE id = '.(int)$this->fs_id);
		$data['stat_bananacount'] = (int)count($data['bananen']);
		
		$data['botschafter'] = false;
		$data['foodsaver'] = false;
		$data['orga'] = false;
		if($bot = $this->q('
			SELECT 	bz.`name`,
					bz.`id` 
				
			FROM 	`'.PREFIX.'bezirk` bz,
					'.PREFIX.'botschafter b 
				
			WHERE 	b.`bezirk_id` = bz.`id` 
			AND 	b.foodsaver_id = '.$this->intval($this->fs_id).'
			AND 	bz.type != 7
		'))
		{
			$data['botschafter'] = $bot;
		}
		if($fs = $this->q('
			SELECT 	bz.`name`,
					bz.`id`
		
			FROM 	`'.PREFIX.'bezirk` bz,
					'.PREFIX.'foodsaver_has_bezirk b
		
			WHERE 	b.`bezirk_id` = bz.`id`
			AND 	b.foodsaver_id = '.$this->intval($this->fs_id).'
			AND 	bz.type != 7
		'))
		{
			$data['foodsaver'] = $fs;
		}
		if($orga = $this->q('
			SELECT 	bz.`name`,
					bz.`id`
		
			FROM 	`'.PREFIX.'bezirk` bz,
					'.PREFIX.'botschafter b
		
			WHERE 	b.`bezirk_id` = bz.`id`
			AND 	b.foodsaver_id = '.$this->intval($this->fs_id).'
			AND 	bz.type = 7
		'))
		{
			$data['orga'] = $orga;
		}
		
		$data['pic'] = false;
		if(!empty($data['photo']) && file_exists('images/'.$data['photo']))
		{
			$data['pic'] = array(
				'original' => 'images/'.$data['photo'],
				'medium' => 'images/130_q_'.$data['photo'],
				'mini' => 'images/50_q_'.$data['photo']
			);
		}
		
		return $data;
	}
	
	public function getProfile()
	{
		return $this->qRow('

			SELECT 	fs.id,
					fs.name,
					fs.nachname,
					fs.geschlecht
				
			FROM 	'.PREFIX.'foodsaver fs
				
			WHERE 	fs.id = '.(int)$this->fs_id.'
				
		');
	}
	
	public function getStats()
	{
		
	}
}