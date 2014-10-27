<?php
class MaintenanceControl extends ConsoleControl
{	
	private $model;
	
	public function __construct()
	{		
		$this->model = new MaintenanceModel();
	}
	
	public function daily()
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
		 * Master Bezirk Update
		 * 
		 * we have master bezirk that mean any user hierarchical under this bezirk have to be also in master self
		 */
		$this->masterBezirkUpdate();
		
		/*
		 * check inactive users and send wake up emails or set in sleeping mode
		 */
		//$this->sleepingMode();
		
	}
	
	public function hourly()
	{
		/*
		 * some updates for new user management
		*/
		$this->model->updateRolle();
	}
	
	public function test()
	{
		$this->model->update('UPDATE fs_foodsaver SET sleep_status = 0');
		$this->sleepingMode();
	}
	
	private function sleepingMode()
	{
		/*
		 * get foodsaver more than 30 days inactive set to sleeping mode and send email
		 */
		
		info('sleeping mode');
		
		$inactive_fsids = array();
		if($foodsaver = $this->model->listFoodsaverInactiveSince(30))
		{
			foreach ($foodsaver as $fs)
			{
				$inactive_fsids[$fs['id']] = $fs['id'];
				$this->tplMail(27, $fs['email'],array(
					'name' => $fs['name'],
					'anrede' => s('anrede_'.$fs['geschlecht'])
				));
				
				$this->infoToBotsUserDeactivated($fs);
			}
			$this->model->setFoodsaverInactive($inactive_fsids);
			
			info(count($inactive_fsids).' user going to sleep..');
		}
		
		/*
		 * get all foodasver theyre dont login since 14 days and send an wake up email
		 */
		if($foodsaver = $this->model->listFoodsaverInactiveSince(14))
		{
			foreach ($foodsaver as $fs)
			{
				$this->tplMail(26, $fs['email'],array(
					'name' => $fs['name'],
					'anrede' => s('anrede_'.$fs['geschlecht'])
				));
			}
			
			info(count($foodsaver).' get an wakeup email..');
		}		
	}
	
	private function infoToBotsUserDeactivated($foodsaver)
	{
		if($botschafer = $this->model->getUserBotschafter($foodsaver['id']))
		{			
			$this->model->addBell(
				$botschafer,
				'fs_sleepmode_title',
				'fs_sleepmode',
				'fa fa-user',
				array('href' => '#','onclick' => 'profile('.$foodsaver['id'].');return false;'),
				array('name' => $foodsaver['name'],'nachname' => $foodsaver['nachname'],'id' => $foodsaver['id']),
				'fs-sleep'.(int)$foodsaver['id']
			);
		}
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
	
	public function membackup()
	{
		info('backup memcache to file...');
		
		if($keys = Mem::$cache->getAllKeys())
		{
			$bar = $this->progressbar(count($keys));
			$data = array();
			$i=0;
			foreach ($keys as $key)
			{
				$i++;
				$bar->update($i);
				if(substr($key,0,3) == 'cb-' || substr($key,0,5) == 'user-')
				{
					$data[$key] = Mem::get($key);
				}
			}
			file_put_contents(ROOT_DIR . 'tmp/membackup.ser',serialize($data));
		}
		
		echo "\n";
		success('OK');
	}
	
	public function memrestore()
	{
		info('backup memcache from file...');
		if($data = file_get_contents(ROOT_DIR . 'tmp/membackup.ser'))
		{
			$data = unserialize($data);
			
			$bar = $this->progressbar(count($data));
			$i=0;
			
			$this_night_ts = (mktime (5, 0, 0, date('n'), date('j'),date('Y')) + (24*60*60));
			
			foreach ($data as $key => $val)
			{
				$i++;
				$bar->update($i);
				
				$ttl = 0;
				
				Mem::set($key, $val,$ttl);
			}
		}
		
		echo "\n";
		success('OK');
	}
}
