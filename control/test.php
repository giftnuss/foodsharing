<?php
//makeToken();
//wartung();
//badmail();

function badmail()
{
	global $db;
	if($boxes = $db->qCol('
		SELECT id
		FROM `fs_mailbox`
		WHERE name LIKE "%."		
	'))
	{
		echo count($boxes).' Boxen';
		
		echo $db->update('UPDATE fs_foodsaver SET mailbox_id = 0 WHERE mailbox_id IN('.implode(',', $boxes).')').' update_fs:: ';
		echo $db->update('UPDATE fs_bezirk SET mailbox_id = 0 WHERE mailbox_id IN('.implode(',', $boxes).')').' update_bz:: ';
		
		echo $db->update('DELETE FROM fs_mailbox_member WHERE mailbox_id  IN('.implode(',', $boxes).')').' del_mb:: ';
		
		echo $db->del('DELETE FROM fs_mailbox WHERE id IN('.implode(',', $boxes).')').' del:: ';
		
		foreach ($boxes as $b)
		{
			
		}
	}
}

function lastLogin()
{
	global $db;
	if($foodsaver = $db->q('SELECT foodsaver_id, MAX(time) AS `time` FROM fs_login GROUP BY foodsaver_id'))
	{
		foreach ($foodsaver as $fs)
		{
			$db->update('
				UPDATE fs_foodsaver SET last_login = "'.$fs['time'].'"	
				WHERE 	id = '.$fs['foodsaver_id'].'	
			');
		}
	}
}

function makeToken()
{
	global $db;
	$foodsaver = $db->q('
		SELECT id FROM fs_foodsaver		
	');
	
	foreach ($foodsaver as $fs)
	{
		echo '.';
		$db->update('
			UPDATE 	`fs_foodsaver` 
			SET 	`token` = '.$db->strval(uniqid('',true)).' 
			WHERE 	id = '.(int)$fs['id']
		);
	}
}

function fillFsId()
{
	global $db;
	
	if($foodsaver = $db->q('
		SELECT 	`id`,`data`,`fs_id`,`stadt`
		FROM `'.PREFIX.'foodsaver`
	'))
	{
		$upfscount=0;
		$uportcount=0;
		foreach ($foodsaver as $fs)
		{
			if((int)$fs['fs_id'] == 0)
			{
				if(!empty($fs['data']))
				{
					$data = json_decode($fs['data'],true);
					if(!isset($data['from_google']))
					{
						if((int)$data['fs_id'] > 0)
						{
							$db->update('
								UPDATE `'.PREFIX.'foodsaver`
								SET `fs_id` = '.(int)$data['fs_id'].'
								WHERE `id` = '.$fs['id'].'		
							');
							$upfscount++;
						}
					}
				}
			}
			
			if(empty($fs['stadt']))
			{
				if(!empty($fs['data']))
				{
					$data = json_decode($fs['data'],true);
					if(!isset($data['from_google']))
					{
						if(!empty($data['stadt']))
						{
							$db->update('
								UPDATE `'.PREFIX.'foodsaver`
								SET `stadt` = '.$db->strval($data['stadt']).'
								WHERE `id` = '.$fs['id'].'
							');
							$uportcount++;
						}
					}
					else 
					{
						print_r($data);
						die();
					}
				}
			}
		}
		
		echo $upfscount.' fsids geupdatet! '.$uportcount.' Stadt updates';
	}
	
	exit;
}

function findLoneyBetrieb()
{
	global $db;
	if($betriebe = $db->q('SELECT `id`,`name` FROM `fs_betrieb`'))
	{
		foreach ($betriebe as $b)
		{
			if(!$db->qOne('SELECT `foodsaver_id` FROM `fs_betrieb_team` WHERE `betrieb_id` = '.$b['id'].' AND verantwortlich = 1'))
			{
				if($fsid = $db->qOne('SELECT foodsaver_id FROM fs_betrieb_notiz WHERE betrieb_id = '.$b['id'].' AND text = "{BETRIEB_ADDED}"'))
				{
					echo $b['name'].':'.$b['id'].':'.$fsid.':hat nun den eintrager als v<br />';
					$db->insert('
						REPLACE INTO fs_betrieb_team
						(
							`betrieb_id`,
							`foodsaver_id`,
							`verantwortlich`,
							`active`
						)
						VALUES('.$b['id'].','.$fsid.',1,1)
					');
				}
				else
				{
					$bezirk_id = $db->getVal('bezirk_id', 'betrieb', $b['id']);
					$verantwortliche = array();
					if($botschafter = $db->getBotschafter($bezirk_id))
					{
						$verantwortliche = $botschafter;
						echo $b['name'].':'.$b['id'].':'.$fsid.':nix zu machen Botschafter werden informiert<br />';
					}
					else 
					{
						$verantwortliche = $db->getOrgateam();
						echo $b['name'].':'.$b['id'].':'.$fsid.':nix zu machen Orga wird informiert<br />';
					}
					
					foreach ($verantwortliche as $v)
					{
						$db->insert('
						REPLACE INTO fs_betrieb_team
						(
							`betrieb_id`,
							`foodsaver_id`,
							`verantwortlich`,
							`active`
						)
						VALUES('.$b['id'].','.$v['id'].',1,1)
					');
						
						tplMail(14, $v['email'],array(
							'anrede' => genderWord($v['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
							'name' => $v['name'],
							'betrieb' => $b['name'],
							'link' => BASE_URL.'?page=fsbetrieb&id='.$b['id']
						));
						echo $v['name'].':'.$v['email'].':sende...<br />';
					}
					
				}
			}
		}
	}
	die();
}

function update_betrieb_notiz()
{
	global $db;
	if($ret = $db->q('
		SELECT MAX(`id`) AS id, betrieb_id FROm fs_betrieb_notiz GROUP BY(`betrieb_id`)		
	'))
	{
		$db->update('
			UPDATE 	fs_betrieb_notiz
			SET 	last = 0	
		');
		foreach ($ret as $r)
		{
			$db->update('
			UPDATE 	fs_betrieb_notiz
			SET 	last = 1
			WHERE 	`id` = '.$r['id'].'
		');
		}
	}
}

function linkforumbezirk()
{
	global $db;
	if($threads = $db->q('SELECT `id`,`bezirk_id` FROM `fs_theme`'))
	{
		foreach ($threads as $t)
		{
			$db->insert('
				INSERT INTO `fs_bezirk_has_theme`
				(
					`bezirk_id`,
					`theme_id`
				)
				VALUES('.$t['bezirk_id'].','.$t['id'].')
			');
		}
	}
}

function u_update4()
{
	global $db;
	if($betriebe = $db->q('SELECT * FROM `fs_betrieb`'))
	{
		foreach ($betriebe as $b)
		{
			if($b['added'] == '0000-00-00')
			{
				
				$newdate = date('Y-m-d');
				if($b['status_date'] != '0000-00-00')
				{
					$newdate = $b['status_date'];
				}
				
				$db->update('UPDATE `fs_betrieb` SET `added` = "'.$newdate.'" WHERE id = '.(int)$b['id']);
			}
		}
	}
}

function u_update3()
{
	global $db;
	
	if($foodsaver = $db->q('
			SELECT MAX( `date` ) AS date, foodsaver_id
			FROM `fs_pass_gen`
			GROUP BY `foodsaver_id`'
	))
	{
		foreach ($foodsaver as $fs)
		{
			echo $db->update('UPDATE `'.PREFIX.'foodsaver` SET `last_pass` = "'.$fs['date'].'" WHERE `id` = '.$fs['foodsaver_id'].'').'<br />';
		}
	}
}

function u_update2()
{
	$i=0;
	global $db;
	$foodsaver = $db->q('SELECT `plz`,`id`,`stadt` FROM `fs_foodsaver` WHERE `stadt` LIKE "%Berlin%"');
	
	foreach ($foodsaver as $fs)
	{
		
			$check = true;
				$db->update('
					UPDATE `fs_foodsaver` SET `bezirk_id` = 47  WHERE `stadt` LIKE "%Berlin%" AND bezirk_id = 0
				');
			$i++;
			
		
	}
	
	echo $i.'<--';
}

function u_update()
{
	$i=0;
	global $db;
	$foodsaver = $db->q('SELECT `plz`,`id`,`stadt` FROM `fs_foodsaver` WHERE `bezirk_id` = 0');
	
	foreach ($foodsaver as $fs)
	{
		$check =false;
		if(!empty($fs['stadt']))
		{
			if($bid = $db->qOne('SELECT `id` FROM `fs_bezirk` WHERE `name` LIKE "'.$fs['stadt'].'" '))
			{
				$check = true;
				$db->update('
					UPDATE `fs_foodsaver` SET `bezirk_id` = '.$bid.' WHERE `id` = '.$fs['id'].'		
				');
				$i++;
			}
		}
		
		if(!$check)
		{
			if($stadt = $db->qOne('
				SELECT 	s.`name`

				FROM 	fs_stadt s,
						fs_plz p
					
				WHERE 	s.id = p.stadt_id
				AND 	p.plz = "'.preg_replace('/[^0-9]/', '', $fs['plz']).'"
			'))
			{
				if($bid = $db->qOne('SELECT `id` FROM `fs_bezirk` WHERE `name` LIKE "'.$stadt.'" '))
				{
					$check = true;
					$db->update('
						UPDATE `fs_foodsaver` SET `bezirk_id` = '.$bid.' WHERE `id` = '.$fs['id'].'		
					');
					$i++;
				}
			}	
		}
	}
	
	echo $i.'<--';
	
}