<?php
class MaintenanceControl extends ConsoleControl
{	
	private $model;
	
	public function __construct()
	{		
		$this->model = new MaintenanceModel();
	}
	
	public function index()
	{
		/*
		 * update bezirk ids
		 * there is this old 1:n relation foodsaver <=> bezirk we just check in one step the relation table
		 */
		$this->updateBezirkIds();
		
		/*
		 * fill memcache with info about users if they want information mails etc..
		 */
		$this->memcacheUserInfo();
		
		/*
		 * delete old bells
		 */
		$this->deleteBells();
		
		/*
		 * delete unuser images
		 */
		$this->deleteImages();
		
		
		/*
		 * delete unconfirmed betrieb dates in the past
		 */
		$this->deleteUnconformedFetchDates();
		
		/*
		 * deactivate too old food baskets
		 */
		$this->deactivateBaskets();
		
		/*
		 * some updates for new user management
		 */
		$this->model->updateRolle();
		
		/*
		 * Master Bezirk Update
		 * 
		 * we have master bezirk that mean any user hierarchical under this bezirk have to be also in master self
		 */
		$this->masterBezirkUpdate();
	}
	
	private function deactivateBaskets()
	{
		$count = $this->model->deactivateOldBaskets(14);
		info($count.' old foodbaskets deactivated');
	}
	
	private function deleteBells()
	{
		if($ids = $this->model->listOldBellIds())
		{
			$this->model->deleteBells($ids);	
			info(count($ids).' old bells deleted');		
		}
	}
	
	private function deleteUnconformedFetchDates()
	{
		info('delete unfonfirmed fetchdates...');
		$count = $this->model->deleteUnconformedFetchDates();
		success($count.' deleted');
	}
	
	private function deleteImages()
	{
		@unlink('images/.jpg');
		@unlink('images/.png');
		
		/* foodsaver photos */
		if($foodsaver = $this->model->q('SELECT id, photo FROM '.PREFIX.'foodsaver WHERE photo != ""'))
		{
			$update = array();
			foreach ($foodsaver as $fs)
			{
				if(!file_exists('images/'.$fs['photo']))
				{
					$update[] = $fs['id'];
				}
			}
			if(!empty($update))
			{
				$this->model->update('UPDATE '.PREFIX.'foodsaver SET photo = "" WHERE id IN('.implode(',', $update).')');
			}
		}
		$check = array();
		if($foodsaver = $this->model->q('SELECT id, photo FROM '.PREFIX.'foodsaver WHERE photo != ""'))
		{
			foreach ($foodsaver as $fs)
			{
				$check[$fs['photo']] = $fs['id'];
			}
			$dir = opendir('./images');
			$count = 0;
			while (($file = readdir($dir)) !== false)
			{
				if(strlen($file) > 3 && !is_dir('./images/'.$file))
				{
					$cfile = $file;
					if(strpos($file, '_') !== false)
					{
						$cfile = explode('_', $file);
						$cfile = end($cfile);
					}
					if(!isset($check[$cfile]))
					{
						$count++;
						@unlink('./images/'.$file);
						@unlink('./images/130_q_'.$file);
						@unlink('./images/50_q_'.$file);
						@unlink('./images/med_q_'.$file);
						@unlink('./images/mini_q_'.$file);
						@unlink('./images/thumb_'.$file);
						@unlink('./images/thumb_crop_'.$file);
						@unlink('./images/q_'.$file);
					}
				}
			}
		}
	}
	
	private function checkAvatars()
	{
		if($foodsaver = $this->model->listAvatars())
		{
			$nophoto = array();
			foreach ($foodsaver as $fs)
			{
				if(file_exists('images/' . $fs['photo']))
				{
					if(!file_exists('images/50_q_' . $fs['photo']))
					{
						copy('images/' . $fs['photo'], 'images/50_q_' . $fs['photo']);
						$photo = new fImage('images/50_q_' . $fs['photo']);
						$photo->cropToRatio(1, 1);
						$photo->resize(50, 50);
						$photo->saveChanges();
					}
				}
				else
				{
					$nophoto[] = (int)$fs['id'];
				}
			}
			
			if(!empty($nophoto))
			{
				$this->model->noAvatars($nophoto);
				info(count($nophoto).' foodsaver noavatar updates');
			}
		}
	}
	
	private function memcacheUserInfo()
	{
		if($foodsaver = $this->model->getUserInfo())
		{
			foreach ($foodsaver as $fs)
			{
				$info = false;
				if($fs['infomail_message'])
				{
					$info = true;
				}
					
				Mem::userSet($fs['id'], 'infomail', $info);
			}
			
			info('memcache userinfo updated');
		}
	}
	
	private function updateBezirkIds()
	{
		$this->model->updateBezirkIds();
		info('bezirk_id relation update');
	}
	
	private function masterBezirkUpdate()
	{
		info('master bezirk update');
		/* Master Bezirke */
		if($foodasver = $this->model->q('
				SELECT
				b.`id`,
				b.`name`,
				b.`type`,
				b.`master`,
				hb.foodsaver_id
		
				FROM 	`'.PREFIX.'bezirk` b,
				`'.PREFIX.'foodsaver_has_bezirk` hb
		
				WHERE 	hb.bezirk_id = b.id
				AND 	b.`master` != 0
				AND 	hb.active = 1
		
		'))
		{
			foreach ($foodasver as $fs)
			{
				if(!$this->model->qRow('SELECT bezirk_id FROM `'.PREFIX.'foodsaver_has_bezirk` WHERE foodsaver_id = '.(int)$fs['foodsaver_id'].' AND bezirk_id = '.$fs['master']))
				{
					$this->model->insert('
					INSERT INTO `'.PREFIX.'foodsaver_has_bezirk`
					(
						`foodsaver_id`,
						`bezirk_id`,
						`active`,
						`added`
					)
					VALUES
					(
						'.(int)$fs['foodsaver_id'].',
						'.(int)$fs['master'].',
						1,
						NOW()
					)
					');
				}
			}
		}
		
		success('OK');
	}
}
