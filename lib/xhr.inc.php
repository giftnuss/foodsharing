<?php
$db = new ManualDb();

$xhr_script = '';

function xhr_verify($data)
{
	$fsmodel = loadModel('foodsaver');
	$bids = $fsmodel->getFsBezirkIds((int)$data['fid']);

	if(isBotForA($bids, false, true) || isOrgateam())
	{
		global $db;
		if($db->update('UPDATE `'.PREFIX.'foodsaver` SET `verified` = '.(int)$data['v'].' WHERE `id` = '.(int)$data['fid']))
		{
			
			$db->insert('
			INSERT INTO 	`'.PREFIX.'verify_history`
			(
				`fs_id`,
				`date`,
				`bot_id`,
				`change_status`
			)
			VALUES
			(
				'.(int)$data['fid'].',
				NOW(),
				'.fsId().',
				'.(int)$data['v'].'
			)
		');
			$model = loadModel();
			$model->delBells('new-fs-'.(int)$data['fid']);
			return json_encode(array(
				'status' => 1
			));
		}
	}else{
		return json_encode(array(
				'status' => 0
			));
	}
}

function xhr_stringEditor($data)
{
	if(isOrgateam())
	{
		$page = preg_replace('/[^a-zA-Z0-9_]/', '', $data['page']);
		
		$lang = 'DE';

		$out = array();
		$script = '';
		if(file_exists('lang/'.$lang.'/'.$page.'.lang.php'))
		{
			if(isset($_POST['form_submit']) && isset($_POST['string']) && !empty($_POST['string']))
			{
				$code = "<?php \n";
				$code .= 'global $g_lang;'."\n";
				foreach($_POST['string'] as $name => $text)
				{
					$code .= '$g_lang[\''.$name.'\'] = \''.$text.'\';'."\n";
				}
				
				$code .= "\n ?>";
				
				file_put_contents('lang/'.$lang.'/'.$page.'.lang.php', $code);
				
				info('Texte gespeichert');
				go($_SERVER['HTTP_REFERER']);
			}
			
			include 'lang/'.$lang.'/'.$page.'.lang.php';
			
			$after_general = false;
			
			foreach ($g_lang as $name => $text)
			{
				
				
				if($after_general)
				{
					$out[] = v_input_wrapper($name, '<textarea id="string-'.$name.'" name="string['.$name.']" class="input text value">'.$text.'</textarea>');
				}
				if($name == '||ENDE||')
				{
					$after_general = true;
				}
			}
			
		}

		
		return json_encode(array(
				'script' => $script,
				'html' => '
					<div class="popbox">
						<h3>Texte aus dem Modul "'.$page.'"</h3>
						<p class="subtitle">Tip: zum Text Suchen die Browser Suche (STRG+F) benutzen :)</p>
						'.v_form('string-edit-form',$out,array('submit'=>'Speichern')).'
					</div>'
		));
	}
	
}

function xhr_getPinPost($data)
{
	global $db;
	incLang('betrieb');
	incLang('fsbetrieb');
	
	if($db->isInTeam($data['bid']) || isBotschafter() || isOrgaTeam())
	{
		if($out = $db->q('
				SELECT 	n.id,
						n.`text`,
						fs.name,
						fs.id AS fsid,
						UNIX_TIMESTAMP(n.zeit) AS zeit,
						fs.photo,
						n.milestone
									
				FROM  	'.PREFIX.'betrieb_notiz n,
						'.PREFIX.'foodsaver fs
				
				WHERE fs.id = n.foodsaver_id
				AND n.betrieb_id = '.(int)$data['bid'].'
				
				ORDER BY n.zeit DESC
				
				LIMIT 50'))
		{
			//$out = array_reverse($out);
			$html = '<table class="pintable">';
			$odd = 'odd';
			foreach ($out as $o)
			{
				if($odd == 'odd')
				{
					$odd = 'even';
				}
				else
				{
					$odd = 'odd';
				}
				$pic = img($o['photo']);
				
				$delete = '';
				if(isOrgaTeam() || fsId() == $o['fsid'])
				{
					$delete = '<span class="dot">·</span><a class="pdelete light" href="#p'.$o['id'].'" onclick="u_delPost('.(int)$o['id'].');return false;">'.s('delete').'</a>';
				}
				
				$msg = '<span class="msg">'.$o['text'].'</span>
						<div class="foot">
							<span class="time">'.format_dt($o['zeit']).' von '.$o['name'].'</span>'.$delete.'
						</div>';
				
				if($o['milestone'] == 1)
				{
					$odd .= ' milestone';
					
					
					
					$msg = '
					<div class="milestone">
						<a href="#" onclick="profile('.(int)$o['fsid'].');return false;">'.$o['name'].'</a> '.sv('betrieb_added',format_d($o['zeit'])).'
					</div>';
					
					$pic = 'img/milestone.png';
					
				}
				elseif($o['milestone'] == 2)
				{
					$odd .= ' milestone';
					$msg = '<span class="msg">'.sv('accept_request','<a href="#" onclick="profile('.(int)$o['fsid'].');return false">'.$db->getVal('name', 'foodsaver', $o['fsid']).'</a>').'</span>';
				}
				elseif($o['milestone'] == 3)
				{
					$odd .= ' milestone';
					$pic = 'img/milestone.png';
					$msg = '<span class="msg"><strong>'.sv('status_change_at',format_d($o['zeit'])).'</strong> '.s($o['text']).'</span>';
				}
				elseif($o['milestone'] == 5)
				{
					$odd .= ' milestone';
					$msg = '<span class="msg">'.sv('quiz_dropped','<a href="#" onclick="profile('.(int)$o['fsid'].');return false">'.$db->getVal('name', 'foodsaver', $o['fsid']).'</a>').'</span>';
				}
				
				$html .= '
					<tr class="'.$odd.' bpost bpost-'.$o['id'].'">
						<td class="img"><a href="#"><img src="'.$pic.'" onclick="profile('.(int)$o['fsid'].');return false;" /></a></td>
						<td>'.$msg.'</td>
					</tr>';
			}
			
			
			return json_encode(array(
				'status' => 1,
				'html' => $html.'</table>'
			));
		}
	}
	return json_encode(array(
			'status' => 0
	));
}

function xhr_activeSwitch($data)
{
	$allowed = array(
		'blog_entry' => true
	);
	
	if(may())
	{
		if(isset($allowed[$data['t']]))
		{
			global $db;
			if($db->update('UPDATE `'.PREFIX.$data['t'].'` SET `active` = '.(int)$data['value'].' WHERE `id` = '.(int)$data['id']))
			{
				return 1;
			}
		}
	}
	
	return 0;
}

function xhr_grabInfo($data)
{	
	if(S::may())
	{
		Mem::delPageCache('/?page=dashboard');
		global $db;
		$fields = unsetAll($data, array('photo_public','bundesland_id','lat','lon','stadt','plz','anschrift'));
		
		if($db->updateFields($fields, 'foodsaver', fsId()))
		{
			return xhr_out();
		}
	}
}

function xhr_addPinPost($data)
{
	global $db;
	if($db->isInTeam($data['bid']) || isOrgaTeam() || isBotschafter())
	{
		if(isset($_SESSION['last_pinPost']))
		{			if((time()-$_SESSION['last_pinPost']) < 2)
		{
			return xhr_getPinPost($data);
		}
		}
		if($db->add_betrieb_notiz(array(
				'foodsaver_id' =>fsId(),
				'betrieb_id' => $data['bid'],
				'text' => $data['text'],
				'zeit' => date('Y-m-d H:i:s'),
				'milestone' => 0,
				'last' => 1
		)))
		{
			$poster = $db->getVal('name','foodsaver',fsId());
			$betrieb = $db->getVal('name', 'betrieb', (int)$data['bid']);
			//$db->addGlocke(explode(',', $data['team']), $betrieb, 'Neuer Pinnwandeintrag von '.$poster,'/?page=fsbetrieb&id='.(int)$data['bid']);
			$_SESSION['last_pinPost'] = time();
			return xhr_getPinPost($data);
		}
		
	}
}

function xhr_childBezirke($data)
{
	if(isset($data['parent']))
	{
		global $db;
		$sql = ' AND 		`type` != 7';
		if(isOrgaTeam())
		{
			$sql = '';
		}
		if($childs = $db->q('SELECT `id`,`parent_id`,`has_children`,`name`,`type` FROM `'.PREFIX.'bezirk` WHERE `parent_id` = '.$db->intval($data['parent']).$sql))
		{
			return json_encode(array(
				'status' => 1,
				'html' => xv_childBezirke($childs,$data['parent'])
			));
		}
		else
		{
			return json_encode(array(
				'status' => 0
			));
		}
	}
}

function xhr_profile($data)
{
	global $db;
	
	$foodsaver = $db->getOne_foodsaver($data['id']);
	
	$bezirk = $db->getBezirk($foodsaver['bezirk_id']);
	
	//print_r($foodsaver);
	
	$subtitle = '';
	if(isset($foodsaver['botschafter']))
	{
		$subtitle = 'ist '.genderWord($foodsaver['geschlecht'], 'Botschafter', 'Botschafterin', 'Botschafter/in').' f&uuml;r ';
		foreach ($foodsaver['botschafter'] as $i => $b)
		{
			$sep = ', ';
			
			if($i == (count($foodsaver['botschafter'])-2))
			{
				$sep = ' und ';
			}
			
			$subtitle .= $b['name'].$sep;
		}
		
		$subtitle = substr($subtitle, 0,(strlen($subtitle)-2));
		if($foodsaver['orgateam'] == 1)
		{
			$subtitle .= ', außerdem engagiert '.genderWord($foodsaver['geschlecht'], 'er', 'sie', 'er/sie').' sich Foodsharing Orgateam';
		}
	}
	elseif($foodsaver['bezirk_id'] == 0)
	{
		$subtitle = 'hat sich bisher für keinen Bezirk entschieden.';
	}
	else
	{
		$subtitle = 'ist '.genderWord($foodsaver['geschlecht'], 'Foodsaver', 'Foodsaverin', 'Foodsaver').' für '.$bezirk['name'];
	}
	
	$photo = img($foodsaver['photo'],130,'q');
	$data = array();
	
	if((isBotschafter() || isOrgaTeam() || isset($foodsaver['botschafter'])))
	{
		if(!empty($foodsaver['handy']))
		{
			$data[] = array('name' => 'Handy', 'val' => $foodsaver['handy']);
		
		}
		if(!empty($foodsaver['telefon']))
		{
			$data[] = array('name' => 'Telefon', 'val' => $foodsaver['telefon']);
		}
		if(isOrgaTeam())
		{
			$data[] = array('name' => 'E-Mail Adresse', 'val' => $foodsaver['email']);
			$data[] = array('name' => 'Adresse', 'val' => $foodsaver['anschrift'].'<br />'.$foodsaver['plz'].' '.$foodsaver['stadt']);
		}
	}
	

	$about = array();
	$about[] = array('name'=>'Rolle','val' => $foodsaver['name'].' '.$subtitle);
	
	if(strlen($foodsaver['about_me_public']) > 3)
	{
		$about[] = array('name'=>'Über '.$foodsaver['name'],'val' => $foodsaver['about_me_public']);
		
	}
	
	$pers = xv_set($about,$foodsaver['name'].' '.$foodsaver['nachname']);
	
	$thead = '';
	$tbody = '';
	
	if(isOrgaTeam())
	{
		$fsdata = json_decode($foodsaver['data'],true);
		$detail = '';
		
		if(isset($fsdata['from_google']))
		{
			$fsdata = $fsdata['from_google'];
			
			foreach ($fsdata as $key => $v)
			{
				if(is_array($v))
				{
					$v = '<pre>'.print_r($v,true).'</pre>';
				}
				$detail .= '<p>'.s($key).':<br />'. $v.'</p>';
			}
			
			$detail = v_input_wrapper('Daten vom Google-Formular', $detail);
		}
		else
		{
			$detail = v_input_wrapper('Daten aus Anmeldeformular', '<pre>'.print_r($fsdata,true).'</pre>');
		}

		$thead = '<li><a href="#ptab-'.(int)$foodsaver['id'].'-2">Details</a></li>';
		$tbody = '
		<div id="ptab-'.(int)$foodsaver['id'].'-2">
			<div style="overflow:auto;height:400px;">
				<pre>'.$detail.'</pre>
			</div>
		</div>';
	}
	
	$edit = '';
	if(isOrgaTeam() || isBotschafter())
	{
		$edit = '<li><a href="/?page=foodsaver&a=edit&id='.$foodsaver['id'].'">bearbeiten</a></li>';
	}
	
	return json_encode(array(
		'status' => 1,
		'html' => '
			<div id="dialog-profile-info">
				<div id="tabs-profile">
			    	<ul>
			      		<li><a href="#ptab-'.(int)$foodsaver['id'].'-1">'.$foodsaver['name'].'</a></li>
						'.$thead.'
			    	</ul>
			    	<div id="ptab-'.(int)$foodsaver['id'].'-1">
					<div class="xv_left">
						<img src="'.$photo.'" alt="'.$foodsaver['name'].' '.$foodsaver['nachname'].'" />
						<ul>
							<li><a onclick="chat('.(int)$foodsaver['id'].');return false;" href="#">Nachricht schreiben</a></li>
							'.$edit.'
						</ul>
					</div>
					
					'.xv_set($data,'Kontaktdaten').'
					<div style="clear:both;"></div>
						'.$pers.'
					</div>
				</div>',
			
		'script' => ''
	));
	
}
/*
 * 
			
				<ul>
					<li><a href="#ptab-'.(int)$foodsaver['id'].'-1">Profil</a></li>
					'.$thead.'
					<li class="ui-tab-dialog-close"></li>
				</ul>
				<div id="ptab-'.(int)$foodsaver['id'].'-1">
					<div class="xv_left">
						<img src="'.$photo.'" alt="'.$foodsaver['name'].' '.$foodsaver['nachname'].'" />
						<ul>
							<li><a onclick="chat('.(int)$foodsaver['id'].');return false;" href="#">Nachricht schreiben</a></li>
							'.$edit.'
						</ul>
					</div>
					
					'.xv_set($data,'Kontaktdaten').'
					<div style="clear:both;"></div>'.
					$pers.'
				</div>
				'.$tbody.'
 */

function addXhJs($js)
{
	global $xhr_script;
	$xhr_script .= $js;
}

function xhr_jsonTeam($data)
{
	global $db;
	$fs = $db->q(' SELECT fs.`id`,CONCAT(fs.`name`," ",fs.`nachname`) AS name FROM '.PREFIX.'foodsaver fs WHERE `active` = 1 ');

	return 'var foodsaver = '.json_encode($fs);
}

function xhr_jsonFsMaps($data)
{
	global $db;
	$fs = '';
	if((isBotschafter() || isOrgaTeam() || S::may('fs') ||isset($foodsaver['botschafter'])))
	{
	$fs = $db->q(' SELECT `id`,lat,lon FROM '.PREFIX.'foodsaver WHERE `active` = 1 AND lat != "" ');
	}

	return 'var fsMaps = '.json_encode($fs);
}

function xhr_jsonBetriebe($data)
{
	global $db;
	$b = '';
	if((isBotschafter() || isOrgaTeam() || S::may('fs') || isset($foodsaver['botschafter'])))
	{
	$b = $db->q(' SELECT `id`,lat,lon FROM '.PREFIX.'betrieb WHERE lat != "" ');
	}
	return 'var g_betriebe = '.json_encode($b);
}

function xhr_bBubble($data)
{
	if(S::may('fs'))
	{
		global $db;
		if($b = $db->getMyBetrieb($data['id']))
		{
			$b['inTeam'] = false;
			if($db->isInTeam($b['id']))
			{
				$b['inTeam'] = true;
			}
			return json_encode(array(
					'status' => 1,
					'html' => xv_bBubble($b),
					'betrieb' => array(
							'name' => $b['name']
					)
			));
		}
	}
	
	return json_encode(array('status'=>0));
}

function xhr_fsBubble($data)
{
	global $db;
	if($b = $db->getOne_foodsaver($data['id']))
	{
		return json_encode(array(
				'status' => 1,
				'html' => xv_fsBubble($b)
		));
	}

	return json_encode(array('status'=>0));
}

function xhr_jsonBoth($data)
{
	return xhr_jsonFoodsaver($data)."\n".xhr_jsonBetriebe($data);
}

function xhr_jsonFoodsaver($data)
{
	global $db;
	$fs = '';
	if((isBotschafter() || isOrgaTeam() || S::may('fs') || isset($foodsaver['botschafter'])))
	{
	$fs = $db->q(' SELECT `id`, `photo_public`,`lat`,`lon` FROM `'.PREFIX.'foodsaver` WHERE `active` = 1 AND lat != "" ');
	}
	
	return 'var foodsaver = '.json_encode($fs);
}

function xhr_loadMarker($data)
{
	global $db;
	$out = array();
	$out['status'] = 0;
	if(isset($data['types']) && is_array($data['types']))
	{
		$out['status'] = 1;
		foreach ($data['types'] as $t)
		{
			if($t == 'foodsaver')
			{
				$out['foodsaver'] = $db->q(' SELECT `id`, `photo_public`,`lat`,`lon` FROM `'.PREFIX.'foodsaver` WHERE `active` = 1 AND rolle IN(1,2,3,4) AND `photo_public` != 4 AND lat != "" ');
			}
			elseif($t == 'betriebe')
			{
				$team_status = array();
				$nkoorp = '';
				if(isset($data['options']) && is_array($data['options']))
				{
					foreach ($data['options'] as $opt)
					{
						if($opt == 'needhelpinstant')
						{
							$team_status[] = 'team_status = 2';
						}
						else if($opt == 'needhelp')
						{
							$team_status[] = 'team_status = 1';
						}
						elseif($opt == 'nkoorp')
						{
							$nkoorp = ' AND betrieb_status_id NOT IN(3,5)';
						}
					}
				}
				
				if(!empty($team_status))
				{
					$team_status = ' AND ('.implode(' OR ', $team_status).')';
				}
				else
				{
					$team_status = '';
				}
				
				$out['betriebe'] = $db->q(' SELECT `id`,lat,lon FROM '.PREFIX.'betrieb WHERE lat != "" '.$team_status.$nkoorp);
			}
			elseif($t == 'botschafter')
			{
				$out['botschafter'] = $db->q(' 
						
						SELECT DISTINCT fs.`id`, fs.`photo_public`,fs.`lat`,fs.`lon` 
						FROM 	`'.PREFIX.'foodsaver` fs,
								`'.PREFIX.'botschafter` b,
								`'.PREFIX.'bezirk` bz
						 
						WHERE 	fs.`id` = b.`foodsaver_id` 
						AND 	b.bezirk_id = bz.id
						AND 	lat != "" 
						AND 	bz.`type` != 7 
				');
			}
			elseif ($t == 'fairteiler')
			{
				$out['fairteiler'] = $db->q(' SELECT `id`,lat,lon,bezirk_id AS bid FROM '.PREFIX.'fairteiler WHERE lat != "" AND status = 1 ');
			}
			else if($t == 'baskets')
			{
				if($baskets = $db->q('
				
					SELECT id, lat, lon, location_type
					FROM '.PREFIX.'basket
					WHERE `status` = 1
					
				'))
				{
					/*
					foreach ($baskets as $key => $b)
					{
						if($b['location_type'] !== 0)
						{
							//
						}
					}
					*/
					$out['baskets'] = $baskets;
				}
			}
		}
	}
	
	return json_encode($out);
}

function xhr_addKette_id($data)
{
	global $db;
	return $db->addBetreibskette($data);
}

function xhr_addBetrieb_kategorie_id($data)
{
	global $db;
	return $db->xhr_add_betrieb_kategorie($data);
}

function xhr_addComment($data)
{
	global $db;
	return $db->addComment($data);
}

function xhr_uploadPicture($data)
{
	$id = strtolower($data['id']);
	$id = preg_replace('/[^a-z0-9_]/', '', $id);
	if(isset($_FILES['uploadpic']) )
	{
		if(is_allowed($_FILES['uploadpic']))
		{
			$datein = str_replace('.jpeg', '.jpg', strtolower($_FILES['uploadpic']['name']));
			$ext = strtolower(substr($datein, strlen($datein)-4, 4));
			
			$path = ROOT_DIR.'images/'.$id;
			
			if(!is_dir($path))
			{
				mkdir($path);
			}
			
			$newname = uniqid().$ext;
			
			move_uploaded_file($_FILES['uploadpic']['tmp_name'], $path.'/orig_'.$newname);
			
			copy($path.'/orig_'.$newname, $path.'/'.$newname);
			$image = new fImage($path.'/'.$newname);
			$image->resize(600, 0);
			
			$image->saveChanges();
			
			if($_GET['crop'] == 1)
			{
				$func = 'pictureCrop';
			}
			elseif(isset($_POST['resize']))
			{
				return xhr_pictureResize(array(
					'img' => $newname,
					'id' => $id,
					'resize' => $_POST['resize']
				));
			}
			
			
			
			return  '<html><head></head><body onload="parent.'.$func.'(\''.$id.'\',\''.$newname.'\');"></body></html>';
			
			echo uniqid();
		}
	}
}

function xhr_cropagain($data)
{
	global $db;
	if($photo = $db->getVal('photo', 'foodsaver', $data['fsid']))
	{
		$path = ROOT_DIR.'images';
		$img = $photo;
		
		$targ_w = $data['w'];
		$targ_h = $data['h'];
		$jpeg_quality = 100;
		
		$ext = explode('.',$img);
		$ext = end($ext);
		$ext = strtolower($ext);
		
		switch($ext)
		{
			case 'gif' : $img_r = imagecreatefromgif($path.'/'.$img); ;break;
			case 'jpg' : $img_r = imagecreatefromjpeg($path.'/'.$img); ;break;
			case 'png' : $img_r = imagecreatefrompng($path.'/'.$img); ;break;
		}
		
		
		$dst_r = @ImageCreateTrueColor( $targ_w, $targ_h );
		
		if(!$dst_r)
		{
			return '0';
		}
		
		imagecopyresampled($dst_r,$img_r,0,0,$data['x'],$data['y'],$targ_w,$targ_h,$data['w'],$data['h']);
		
		$new_path = $path.'/crop_'.$img;

		@unlink($new_path);
		
		switch($ext)
		{
			case 'gif' : imagegif($dst_r, $new_path ); break;
			case 'jpg' : imagejpeg($dst_r, $new_path, $jpeg_quality ); break;
			case 'png' : imagepng($dst_r, $new_path, 0 ); break;
		}
		
		copy($path.'/'.$img, $path.'/thumb_'.$img);
		$image = new fImage($path.'/thumb_'.$img);
		$image->resize(150, 0);
		$image->saveChanges();
		
		copy($path.'/'.$img, $path.'/thumb_crop_'.$img);
		$image = new fImage($path.'/thumb_crop_'.$img);
		$image->resize(200, 0);
		$image->saveChanges();
		
		return '1';
	}
	
	return '0';
}

function xhr_pictureCrop($data)
{
	$data['img'];
	$data['id'];
	/*
	 * [ratio-val] => [{"x":37,"y":87,"w":500,"h":281},{"x":64,"y":0,"w":450,"h":450}]
      [resize] => [250,528]
	 */
	
	$ratio = json_decode($_POST['ratio-val'],true);
	$resize = json_decode($_POST['resize']);
	
	if(is_array($ratio) && is_array($resize))
	{
		foreach ($ratio as $i => $r)
		{
			cropImg(ROOT_DIR.'images/'.$data['id'],$data['img'], $i, $r['x'], $r['y'], $r['w'], $r['h']);
			foreach ($resize as $r)
			{
				copy(ROOT_DIR.'images/'.$data['id'].'/crop_'.$i.'_'.$data['img'], ROOT_DIR.'images/'.$data['id'].'/crop_'.$i.'_'.$r.'_'.$data['img']);
				$image = new fImage(ROOT_DIR.'images/'.$data['id'].'/crop_'.$i.'_'.$r.'_'.$data['img']);
				$image->resize($r, 0);
				$image->saveChanges();
			}
		}
		
		copy(ROOT_DIR.'images/'.$data['id'].'/'.$data['img'], ROOT_DIR.'images/'.$data['id'].'/thumb_'.$data['img']);
		$image = new fImage(ROOT_DIR.'images/'.$data['id'].'/thumb_'.$data['img']);
		$image->resize(150, 0);
		$image->saveChanges();
		
		return '<html><head></head><body onload="parent.pictureReady(\''.$data['id'].'\',\''.$data['img'].'\');"></body></html>';
	}
}

function xhr_pictureResize($data)
{
	$id = $data['id'];
	$img = $data['img'];
	$resize = json_decode($data['resize'],true);
	
	if(is_array($resize))
	{
		foreach ($resize as $r)
		{
			copy(ROOT_DIR.'images/'.$id.'/'.$img, ROOT_DIR.'images/'.$id.'/'.$r.'_'.$img);
			$image = new fImage(ROOT_DIR.'images/'.$id.'/'.$r.'_'.$img);
			$image->resize($r, 0);
			$image->saveChanges();
		}
		
		
	}
	
	copy(ROOT_DIR.'images/'.$id.'/'.$img, ROOT_DIR.'images/'.$id.'/thumb_'.$img);
	$image = new fImage(ROOT_DIR.'images/'.$id.'/thumb_'.$img);
	$image->resize(150, 0);
	$image->saveChanges();
	
	return '<html><head></head><body onload="parent.pictureReady(\''.$id.'\',\''.$img.'\');"></body></html>';
}

function xhr_getNewMsg($data)
{
	if(may())
	{
		$_SESSION['mob'] = $data['mob'];
		$mb = '';
		if(isBotschafter())
		{
			$mb = '<li><a href="/?page=mailbox">'.s('mailboxes').'</a></li>';
		}
		global $db;
		
		$mbmodel = loadModel('mailbox');
		$email_out = '';
		$email_count = 0;
		if($emails = $mbmodel->getNewMessages())
		{
			$email_count = count($emails);
			foreach ($emails as $m)
			{
				if(!empty($m['sender']))
				{
					$m['subject'] = trim($m['subject']);
					$m['subject'] = substr($m['subject'], 0,90);
					$m['sender'] = json_decode($m['sender'],true);
					$m['to'] = json_decode($m['to'],true);
					if(isset($m['sender']['personal']))
					{
						$m['sender'] = $m['sender']['personal'];
					}
					else
					{
						$m['sender'] = $m['sender']['mailbox'].'@'.DEFAULT_HOST;
					}
				}
				$to = '';
				if(!empty($m['to']))
				{
					
					foreach ($m['to'] as $t)
					{
						if(isset($t['personal']))
						{
							$to .= $t['personal'];
						}
						else
						{
							$to .= $t['mailbox'].'@'.$t['host'];
						}
					
					}
				}

				$email_out .= '<li class="msg email"><a href="/?page=mailbox&show='.(int)$m['id'].'"><span class="photo"><img alt="E-Mail" src="img/email-msg.png"></span><span class="subject"><span class="from">An: '.$to.'</span><span class="from">Von: '.$m['sender'].'</span><span class="time">'.msgTime($m['time_ts']).'</span></span><span class="message">'.jsonSafe($m['subject']).'</span><span style="display:block;clear:both;"></span></a></li>';
			}
		}
		
		if($msg = $db->q('
			SELECT 	`'.PREFIX.'message`.`id`,
					`'.PREFIX.'message`.`sender_id`,
					`'.PREFIX.'message`.`msg`,
					`'.PREFIX.'foodsaver`.`photo`,
					`'.PREFIX.'message`.`time`,
					UNIX_TIMESTAMP(`'.PREFIX.'message`.`time`) AS time_ts,
					CONCAT(`'.PREFIX.'foodsaver`.`name`," ",`'.PREFIX.'foodsaver`.`nachname`) AS name
				
			FROM 	`'.PREFIX.'message`,
					`'.PREFIX.'foodsaver`
				
			WHERE 	`'.PREFIX.'message`.`sender_id` = `'.PREFIX.'foodsaver`.`id`
			AND 	`'.PREFIX.'message`.`recip_id` = '.$db->intval(fsId()).'
			AND 	`'.PREFIX.'message`.`unread` = 1 	
				
			ORDER BY time_ts DESC
			LIMIT 	10
		'))
		{
			$out = '<li><p>'.sv('new_message_count',count($msg)+$email_count).'</p></li>';
			foreach($msg as $m)
			{
				if(strlen($m['msg']) > 40)
				{
					$m['msg'] = substr($m['msg'], 0,40).' ...';
				}
				$m['time'] = msgTime($m['time_ts']);
				$out .= '<li class="msg"><a href="#'.$m['sender_id'].'" onclick="chat('.(int)$m['sender_id'].');return false;"><span class="photo"><img alt="avatar" src="'.img($m['photo']).'"></span><span class="subject"><span class="from">'.$m['name'].'</span><span class="time">'.$m['time'].'</span></span><span class="message">'.jsonSafe($m['msg']).'</span><span style="display:block;clear:both;"></span></a></li>';
			}
			
			
			$out .= '<li><a href="/?page=message">'.s('show_all_messages').'</a></li>'.$mb.'<li><a href="/?page=message&a=neu">'.s('write_a_messages').'</a></li>';
			return fJSON::encode(array(
				'status' => 1,
				'html' => $out.$email_out,
				'count' => count($msg)+$email_count,
				'script' => 'if($("#msgBar-badge .bar-msg").text().trim()!="'.count($msg).'"){aNotify();}'
			));
		}
		elseif($email_count == 0)
		{
			$out = '';
			/*
			if($msg = $db->q('
					
				SELECT 	
					`'.PREFIX.'message`.`id`,
					MAX(UNIX_TIMESTAMP(time)) AS time_ts, 
					`time`,
					`'.PREFIX.'foodsaver`.`id` AS `sender_id`,
					CONCAT(`'.PREFIX.'foodsaver`.`name`," ",`'.PREFIX.'foodsaver`.`nachname`) AS name, 
					`'.PREFIX.'foodsaver`.`photo`
					
				FROM 	`'.PREFIX.'message`,
						`'.PREFIX.'foodsaver`
					
				WHERE 	`'.PREFIX.'message`.`sender_id` = `'.PREFIX.'foodsaver`.`id`
				AND 	`'.PREFIX.'message`.`sender_id` != '.(int)fsid().'
				AND (
						`'.PREFIX.'message`.`recip_id` = '.$db->intval(fsId()).'
						OR
						`'.PREFIX.'message`.`sender_id` = '.$db->intval(fsId()).'
					)
					
				GROUP BY `sender_id`
				ORDER BY time_ts DESC
				LIMIT 3

			'))
			{
				foreach($msg as $m)
				{
					$arr = $db->qRow('SELECT msg, time, UNIX_TIMESTAMP(time) AS time_ts FROM `'.PREFIX.'message` WHERE sender_id = '.$m['sender_id'].' AND recip_id = '.(int)fsId().' ORDER BY id DESC LIMIT 1');
					$m['msg'] = $arr['msg'];
					$m['time'] = $arr['time'];
					$m['time_ts'] = strtotime($arr['time']);
					if(strlen($m['msg']) > 40)
					{
						$m['msg'] = substr($m['msg'], 0,40).' ...';
					}
					$m['time'] = msgTime($m['time_ts']);
					$out .= '<li class="msg"><a href="#'.$m['sender_id'].'" onclick="chat('.(int)$m['sender_id'].');return false;"><span class="photo"><img alt="avatar" src="'.img($m['photo']).'"></span><span class="subject"><span class="from">'.$m['name'].'</span><span class="time">'.$m['time'].'</span></span><span class="message">'.jsonSafe($m['msg']).'</span><span style="display:block;clear:both;"></span></a></li>';
				}
			}
			*/
			
			if($emails = $mbmodel->getlastMessages())
			{
				foreach ($emails as $m)
				{
					if(!empty($m['sender']))
					{
						$m['subject'] = trim($m['subject']);
						$m['subject'] = substr($m['subject'], 0,90);
						$m['sender'] = json_decode($m['sender'],true);
						$m['to'] = json_decode($m['to'],true);
						
						if(isset($m['sender']['personal']))
						{
							$m['sender'] = $m['sender']['personal'];
						}
						else
						{
							$m['sender'] = $m['sender']['mailbox'].'@'.DEFAULT_HOST;
						}
					}
			
					$to = '';
					if(!empty($m['to']))
					{
							
						foreach ($m['to'] as $t)
						{
							if(isset($t['personal']))
							{
								$to .= $t['personal'];
							}
							else
							{
								$to .= $t['mailbox'].'@'.$t['host'];
							}
								
						}
					}
					
					$out .= '<li class="msg email"><a href="/?page=mailbox&show='.(int)$m['id'].'"><span class="photo"><img alt="E-Mail" src="img/email-msg.png"></span><span class="subject"><span class="from">An: '.$to.'</span><span class="from">Von: '.$m['sender'].'</span><span class="time">'.msgTime($m['time_ts']).'</span></span><span class="message">'.jsonSafe($m['subject']).'</span><span style="display:block;clear:both;"></span></a></li>';
				}
			}
			
			return fJSON::encode(array(
					'status'=>0,
					'html'=>'<li><p>'.s('no_new_messages').'</p></li>'.$out.$email_out.'<li><a href="/?page=message">'.s('show_all_messages').'</a></li>'.$mb.'<li><a href="/?page=message&a=neu">'.s('write_a_messages').'</a></li>',
					'script'=>'$("#msgBar-badge .bar-msg").css("visibility","hidden")'));
		}
		else
		{
			return fJSON::encode(array(
					'status'=>1,
					'count' => $email_count,
					'html'=>'<li><p>'.sv('new_message_count',$email_count).'</p></li>'.$email_out.'<li><a href="/?page=message">'.s('show_all_messages').'</a></li>'.$mb.'<li><a href="/?page=message&a=neu">'.s('write_a_messages').'</a></li>',
					'script'=>'$("#msgBar-badge .bar-msg").css("visibility","hidden")'));
		}
	}
}

function xhr_update_newbezirk($data)
{
	if(isOrgaTeam())
	{
		global $db;
		$data['name'] = strip_tags($data['name']);
		$data['name'] = str_replace(array('/','"',"'",'.',';'), '', $data['name']);
		$data['has_children'] = 0;
		$data['email_pass'] = '';
		$data['email_name'] = 'Foodsharing '.$data['name'];
	
		if(!empty($data['name']))
		{
			if($out = $db->add_bezirk($data))
			{
				$db->update('UPDATE '.PREFIX.'bezirk SET has_children = 1 WHERE `id` = '.$db->intval($data['parent_id']));
				return json_encode(array(
					'status' => 1,
					'script' => '$("#tree").dynatree("getTree").reload();pulseInfo("'.$data['name'].' wurde angelegt");'
				));
			}
			
			
			
		}
		
	}
}

function xhr_out($html = '')
{
	global $xhr_script;
	
	return json_encode(array(
		'status' => 1,
		'html' => $html,
		'script' => $xhr_script
	));
}

function xhr_getFoodsaver($data)
{
	return xhr_getRecip($data);
}

function xhr_checkOnline($data)
{
	global $db;
	if(isset($data['saver']))
	{
		$saver = explode(',', $data['saver']);
		if(count($saver) > 0)
		{
			$out = array();
			foreach ($saver as $s)
			{
				$on = false;
				if($db->isActive((int)$s))
				{
					$on = true;
				}
				$out[] = array('id'=>$s,'online'=>$on);
			}
			return json_encode(array('status'=>1,'saver'=>$out));
		}
	}
	
	return json_encode(array('status'=>0));
}
function xhr_getRecip($data)
{
	if(may())
	{
		global $db;
		$fs = $db->xhrGetFoodsaver($data);
		
		return json_encode($fs);
	}
		
}

function xhr_getBfoodsaver($data)
{
	if(may())
	{
		global $db;
		
		if(isset($_SESSION['client']['bezirke']) && is_array($_SESSION['client']['bezirke']))
		{
			$data['bid'] = array();
			foreach($_SESSION['client']['bezirke'] as $b);
			{
				$data['bid'][] = $b['id'];
			}
		}
		
		$fs = $db->xhrGetFoodsaver($data);
		
		return json_encode($fs);
	}
}

function xhr_sendMsg($data)
{
	global $db;
	$recip = (int)$data['recip'];
	$msg = strip_tags(urldecode($data['msg']));
	
	mailMessage(fsId(), $recip);
	
	$db->insert('
		INSERT INTO 	`'.PREFIX.'message`
		(`msg`,`sender_id`,`recip_id`,`time`,`unread`)
		VALUES
		('.$db->strval($msg).','.$db->intval(fsId()).','.$db->intval($recip).','.$db->dateval(date('Y-m-d H:i:s')).',1);		
	');
	
	return xhr_out();
}

function xhr_getGlocke($data)
{
	$db = loadModel('buddy');
	$count = 0;
	$status = 0;
	$html = '<li><p>Keine neuen Meldungen</p></li>';
	$rolle = array(
			0 => array(
					0 => 'Freiwillige/r',
					1 => 'Foodsave/r',
					2 => 'Botschafte/r'
			),
			1 => array(
					0 => 'Freiwilliger',
					1 => 'Foodsaver',
					2 => 'Botschafter'
			),
			2 => array(
					0 => 'Freiwillige',
					1 => 'Foodsaverin',
					2 => 'Botschafterin'
			),
			3 => array(
					0 => 'Freiwillige/r',
					1 => 'Foodsave/r',
					2 => 'Botschafte/r'
			)
	);
	$html = '';
	$count = 0;
	$fts = array();
	
	if($buddyreq = $db->getBuddyRequests())
	{
		$status = 1;
		foreach ($buddyreq as $buddy)
		{
			$count++;
			$pic = false;
			if(!empty($buddy['photo']))
			{
				makeThumbs($buddy['photo']);
				$pic = img($buddy['photo']);
			}
				
			$buddy['name'] = explode(' ', $buddy['name']);
			$buddy['name'] = $buddy['name'][0];
			
			if($pic === false)
			{
				$pic = 'img/star_yellow.png';
			}
			$html .= '<li class="msg buddyreq-'.(int)$buddy['id'].'">
			<a href="#" onclick="profile('.(int)$buddy['id'].');return false;">
				<span class="photo">
					<img alt="avatar" src="'.$pic.'">
				</span>
				<span class="subject">
					<span class="from">'.$buddy['name'].' kennt Dich</span>
					<span style="position:absolute;top:15px;right:7px;">
						<button title="Anfrage verwerfen" aria-disabled="false" role="button" class="ui-delbuddyreq ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" onclick="ajreq(\'removeRequest\',{app:\'buddy\',id:'.(int)$buddy['id'].'});return false;" onmouseover="$(this).addClass(\'ui-state-hover\');" onmouseout="$(this).removeClass(\'ui-state-hover\');"><span class="ui-button-icon-primary ui-icon ui-icon-closethick"></span><span class="ui-button-text">entfernen</span></button>
					</span>
				</span>
				<span class="message">Kennst Du '.$buddy['name'].'?</span>
				<span style="display:block;clear:both;"></span>
			</a></li>';
			
			//$html .= xv_glockeMsg('',','#br'.$buddy['id'],'',$pic,'','zeit?','z2','z3');
		}
	}
	
	if(isOrgaTeam())
	{
		if($fairteiler = $db->q('
			SELECT `id`,`name`, add_date FROM `'.PREFIX.'fairteiler` WHERE status = 0		
		'))
		{
			foreach ($fairteiler as $ft)
			{
				$fts[$ft['id']] = $ft['id'];
				$html .= xv_glockeMsg($ft['name'].' wurde vogeschlagen', 'Neuer Fair-Teiler', '/?page=fairteiler&sub=check&id='.(int)$ft['id'], msgTime($ft['add_date']), false);
			}
		}
		if($anm = $db->getNeuanmeldungenOhneBotschafter())
		{
			$count += count($anm);
			$status = 1;
			foreach ($anm as $a)
			{
				$pic = false;
				if(!empty($a['photo']))
				{
					makeThumbs($a['photo']);
					$pic = img($a['photo']);
				}
				$msg = $a['name'].' m&ouml;chte '.$rolle[$a['geschlecht']][$a['rolle']].' werden.';
				$title = 'Neue Anmeldung / Kein Botschafter';
				$href = '/?page=checkReg&id='.$a['id'];
				$time = msgTime($a['anmeldedatum']);
				
				$html .= xv_glockeMsg($msg, $title, $href, $time, $pic);
			}
		}
		
		if($new = $db->qOne('SELECT COUNT(`id`) FROM '.PREFIX.'foodsaver WHERE want_new = 1'))
		{
			if($new > 0)
			{
				$count++;
				$status = 1;
				$msg = $new.' Menschen möchten in ihrem Bezirk aktiv werden.';
				if($new == 1)
				{
					$msg = 'Es möchte jemand in seinem Bezirk aktiv werden';
				}
				$html .= xv_glockeMsg($msg, 'Neue Bezirke?', '/?page=wantNew', '&nbsp;', false);
			}
		}
	}
	
	if(isBotschafter())
	{
		if($fairteiler = $db->q('
			SELECT `id`,`name`, add_date FROM `'.PREFIX.'fairteiler` WHERE bezirk_id IN('.implode(',', $db->getBotBezirkIds()).') AND status = 0 
		'))
		{
			foreach ($fairteiler as $ft)
			{
				if(!isset($fts[$ft['id']]))
				{
					$html .= xv_glockeMsg($ft['name'].' wurde vogeschlagen', 'Neuer Fair-Teiler', '/?page=fairteiler&sub=check&id='.(int)$ft['id'], msgTime($ft['add_date']), false);
				}
			}
		}
		if(fsid() != 3674)
		{
			foreach ($_SESSION['client']['botschafter'] as $botsch)
			{
				if($anm = $db->getNeuanmeldungen($botsch['bezirk_id']))
				{
					$bezirk = $db->getBezirk($botsch['bezirk_id']);
					$count += count($anm);
					$status = 1;
					foreach ($anm as $a)
					{
						$pic = false;
						if(!empty($a['photo']))
						{
							makeThumbs($a['photo']);
							$pic = img($a['photo']);
						}
				
				
				
						$msg = $a['name'].' m&ouml;chte '.$rolle[$a['geschlecht']][$a['rolle']].' werden';
						$title = 'Anmeldung '.$bezirk['name'];
						$href = '/?page=checkReg&id='.$a['id'];
						$time = msgTime($a['anmeldedatum']);
				
						$html .= xv_glockeMsg($msg, $title, $href, $time, $pic);
					}
				}
			}
		}
	}
	
	if($confirm = $db->getMyUnconfirmedFetchRequests())
	{
		$status = 1;
		foreach ($confirm as $c)
		{
			$count++;
			
			$html .= xv_glockeMsg('unbestätigte Abholzeiten', $c['name'], '/?page=fsbetrieb&id='.(int)$c['id'], false, false);
		}
	}
	
	if(fsid() != 3674)
	{
		if($requests = $db->q('SELECT foodsaver_id, bezirk_id, rolle, UNIX_TIMESTAMP(`time`) AS zeit FROM '.PREFIX.'upgrade_request'))
		{
			foreach ($requests as $r)
			{
				if(isBotFor($r['bezirk_id']) || isOrgaTeam() 	)
				{
					$count++;
					$status = 1;
					
					$v = $db->qRow('SELECT CONCAT(`name`," ",`nachname`) AS name, photo FROM '.PREFIX.'foodsaver WHERE id = '.(int)$r['foodsaver_id']);
					
					$html .= xv_glockeMsg($v['name'].' möchte Botschafter/in werden','Upgrade Aufrage','/?page=checkUpgradeRequest&fid='.(int)$r['foodsaver_id'],msgTime($r['zeit']),img($v['photo']));
				}
			}
		}
	}

	if($new = $db->getMyGlocke())
	{
		foreach ($new as $n)
		{
			$count++;
			$status = 1;
			
			$n['url'] = str_replace('?', '?gid='.$n['id'].'&', $n['url']);
			
			$html .= xv_glockeMsg($n['msg'],$n['name'],$n['url'],msgTime($n['time']),false);
		}
	}
	
	if(empty($html))
	{
		$html = '<li> <p>Keine neuen Meldungen</p></li>';
	}
	
	return json_encode(array(
		'status' => $status,
		'count' => $count,
		'html' => $html
	));
}

function xhr_orderWantNew($data)
{
	if(isBotschafter())
	{
		global $db;
		$new_id = $db->insert('
			INSERT INTO `'.PREFIX.'bezirk`
			(parent_id,`name`)
			VALUES
			('.$db->intval($data['parent_id']).','.$db->strval($data['new_bezirk']).')
		');
		
		$fs = explode(';', $data['fs']);
		foreach ($fs as $f)
		{
			$db->update('
				UPDATE `'.PREFIX.'foodsaver`
				SET 	`want_new`	= 0,
						`new_bezirk` = "",
						`bezirk_id` = '.(int)$new_id.'
				WHERE `id` = '.(int)$f.'
					
			');
			
			$saver = $db->getOne_foodsaver($f);
			
			tplMail(6, $saver['email'],array(
				'name' => $saver['name'],
				'anrede' => genderWord($saver['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
				'region' => $data['new_bezirk']
			));
		}
		
		return json_encode(array(
			'status' => 1
		));
		
	}
	/*
	 *  Array
		(
		    [f] => orderWantNew
		    [new_bezirk] => Gummersbach
		    [parent_id] => 45
		    [fs] => 259
		)
	 */
	
}

function xhr_updateChat($data)
{
	
	global $db;
	$recip = (int)$data['fsid'];
	$out = array();
	
	if($db->isNewInConversation($recip) || $data['fu'] == 'true')
	{
		$db->update('UPDATE `'.PREFIX.'message` SET `unread` = 0 WHERE `recip_id` = '.$db->intval(fsId()).' AND `sender_id` = '.$db->intval($recip).' AND `unread` = 1;');
		
		$temp = $db->q('
			SELECT 	`'.PREFIX.'message`.`id`,
					`'.PREFIX.'message`.`sender_id`,
					`'.PREFIX.'message`.`msg`,
					`'.PREFIX.'foodsaver`.`photo`,
					`'.PREFIX.'message`.`time`,
					UNIX_TIMESTAMP(`'.PREFIX.'message`.`time`) AS time_ts,
					CONCAT(`'.PREFIX.'foodsaver`.`name`," ",`'.PREFIX.'foodsaver`.`nachname`) AS name
		
			FROM 	`'.PREFIX.'message`,
					`'.PREFIX.'foodsaver`
		
			WHERE 	`'.PREFIX.'message`.`sender_id` = `'.PREFIX.'foodsaver`.`id`
			AND 	`'.PREFIX.'message`.`recip_id` = '.$db->intval($recip).'
			AND 	`'.PREFIX.'message`.`sender_id` = '.$db->intval(fsId()).'
			ORDER BY time_ts DESC
			LIMIT 	20');
		
		
		if(is_array($temp))
		{
			foreach ($temp as $t)
			{
				$out[date('Y-m-d-H-i-s',$t['time_ts']).'-'.fsId()] = $t;
			}
		}
		
		$temp = $db->q('
			SELECT 	`'.PREFIX.'message`.`id`,
					`'.PREFIX.'message`.`sender_id`,
					`'.PREFIX.'message`.`msg`,
					`'.PREFIX.'foodsaver`.`photo`,
					`'.PREFIX.'message`.`time`,
					UNIX_TIMESTAMP(`'.PREFIX.'message`.`time`) AS time_ts,
					CONCAT(`'.PREFIX.'foodsaver`.`name`," ",`'.PREFIX.'foodsaver`.`nachname`) AS name
		
			FROM 	`'.PREFIX.'message`,
					`'.PREFIX.'foodsaver`
		
			WHERE 	`'.PREFIX.'message`.`sender_id` = `'.PREFIX.'foodsaver`.`id`
			AND 	`'.PREFIX.'message`.`recip_id` = '.$db->intval(fsId()).'
			AND 	`'.PREFIX.'message`.`sender_id` = '.$db->intval($recip).'
			ORDER BY time_ts DESC
			LIMIT 	20');
		if(is_array($temp))
		{
			foreach ($temp as $t)
			{
				$out[date('Y-m-d-H-i-s',$t['time_ts']).'-'.$recip] = $t;
			}
		}
		
		ksort($out);
		
		addXhJs('/*xhr_chat_scroll();*/hideLoader();');
		
		return xhr_out(xv_msgLi($out));
	}
	else
	{
		return json_encode(array(
			'status' => 0
		));
	}
}

function xhr_getMsg($data)
{
	if(may())
	{
		global $db;
		if($recip = $db->qRow('
			SELECT `id`,`name` FROM  `'.PREFIX.'foodsaver` WHERE `id` = '.$db->intval($data['id']).'
		'))
		{
			$me = $db->qRow('SELECT `id`,`name`,`nachname`,`photo` FROM  `'.PREFIX.'foodsaver` WHERE `id` = '.$db->intval(fsId()).'');
			$html = xv_page(
					xv_hidden('xhr_sender_id',$recip['id']).
					xv_msgList().
					xv_textarea('msganswer').
					
					xv_submit('send','
						if($("#msganswer").val() != "" && $("#msganswer").val() != "'.s('msganswer').' ...")
						{
							$(".fancybox-inner").css("overflow","hidden");
							xhrf("sendMsg&recip='.$recip['id'].'&msg=" + encodeURIComponent($("#msganswer").val()));
							$("#xv_message").append(\''.xv_msgAppend("'+nl2br($(\"#msganswer\").val())+'", $me['name'].' '.$me['nachname'], 'gerade eben', img($me['photo'])).'\');
							$("#scrollbar1").tinyscrollbar_update("bottom");
							$("#msganswer").val("");
							$("#msganswer")[0].focus();
							showLoader();	
						}
					'),
					sv('conversation_with',$recip['name']));
				
			addXhJs('showLoader();');
			addXhJs('$("#scrollbar1").tinyscrollbar();$(".fancybox-inner").css("overflow","hidden")');
			return xhr_out($html);
		}
		else
		{
			return json_encode(array('status'=>0));
		}
	}
	
}

function xhr_addPhoto($data)
{
	$data = getPostData();
	
	if(isset($data['fs_id']))
	{
		$user_id = (int)$data['fs_id'];
		
		if(isset($_FILES['photo']) && (int)$_FILES['photo']['size'] > 0)
		{
			global $db;
			$ext = explode('.', $_FILES['photo']['name']);
			$ext = strtolower(end($ext));
			//$bild = uploadPhoto();
			
			//$new_filename = 
			
			@unlink('./images/'.$user_id.'.'.$ext);
			@unlink('./images/'.$file);
			
			$file = makeUnique().'.'.$ext;
			if(move_uploaded_file($_FILES['photo']['tmp_name'], './images/'.$file))
			{	
				
				$image = new fImage('./images/'.$file);
				$image->resize(800, 800);
				$image->saveChanges();
				
				copy('./images/'.$file, './images/thumb_crop_'.$file);
				copy('./images/'.$file, './images/crop_'.$file);
				
				$image = new fImage('./images/thumb_crop_'.$file);
				$image->cropToRatio(35, 45);
				$image->resize(200, 200);
				$image->saveChanges();
				
				$image = new fImage('./images/crop_'.$file);
				$image->cropToRatio(35, 45);
				$image->resize(600, 600);
				$image->saveChanges();
				
				copy('./images/thumb_crop_'.$file, './images/mini_q_'.$file);
				$image = new fImage('./images/mini_q_'.$file);
				$image->cropToRatio(1, 1);
				$image->resize(35, 35);
				$image->saveChanges();
				
				copy('./images/thumb_crop_'.$file, './images/130_q_'.$file);
				$image = new fImage('./images/130_q_'.$file);
				$image->cropToRatio(1, 1);
				$image->resize(130, 130);
				$image->saveChanges();
				
				@unlink('./tmp/tmp_'.$file);
				
				$db->addPhoto($user_id,$file);
				return '<html><head></head><body onload="parent.uploadPhotoReady('.$user_id.',\'./images/mini_q_'.$file.'\');"></body></html>';
			}	
		}
	}
}

function xhr_continueMail($data)
{
	if(isOrgaTeam() || isBotschafter())
	{
		global $db;
		$mail_id = (int)$data['id'];
		
		$mail = $db->getOne_send_email($mail_id);
		
		$bezirk = $db->getMailBezirk(getBezirkId());
		$bezirk['email'] = EMAIL_PUBLIC;
		$bezirk['email_name'] = EMAIL_PUBLIC_NAME;
		$recip = $db->getMailNext($mail_id);
		
		$mbmodel = loadModel('mailbox');
		$mailbox = $mbmodel->getMailbox((int)$mail['mailbox_id']);
		$mailbox['email'] = $mailbox['name'].'@'.DEFAULT_HOST;
		
		$sender = $db->getValues(array('geschlecht','name'),'foodsaver',fsId());
	 
		if(empty($recip))
		{
			return  json_encode(array('status' => 2,'comment'=>'Es wurden alle E-Mails verschickt'));
			exit();
		}
		
		$db->setEmailStatus($mail['id'], $recip, 1);
		
		foreach ($recip as $fs)
		{
			$anrede = 'Liebe/r';
			if($fs['geschlecht'] == 1)
			{
				$anrede = 'Lieber';
			}
			elseif ($fs['geschlecht'] == 2)
			{
				$anrede = 'Liebe';
			}
			else
			{
				$anrede = 'Liebe/r';
			}
			
			$search = array('{NAME}','{ANREDE}','{EMAIL}');
			$replace = array($fs['name'],$anrede,$fs['email']);
			$internal_message = true;
			if(strpos($mail['message'], '{PASSWORD}') !== false)
			{
				$passwd = $db->resetPassword($fs['id'],$fs['email']);
				$mail['message'] = str_replace('{EMAIL}', $fs['email'], $mail['message']);
				$mail['message'] = str_replace('{PASSWORD}', $passwd, $mail['message']);
				
				$internal_message = false;
			}
			
			/*
			 *  [id] => 1
			    [foodsaver_id] => 57
			    [complete] => 0
			    [name] => sdf
			    [message] => sdf
			    [zeit] => 2013-08-11 09:34:34
			    [recip] => 
			    [attach] => 
			 */
			
			$attach = false;
			if(!empty($mail['attach']))
			{
				$attach = json_decode($mail['attach'],true);
			}
			
			$message = str_replace($search,$replace, $mail['message']);
			$subject = str_replace($search, $replace, $mail['name']);
			
			//$fs['email'] = 'kontakt@prographix.de';
			$check = false;
			if($mail['mode'] == 2)
			{
				if(libmail($mailbox, $fs['email'], $subject, $message, $attach, $fs['token']))
				{
					$check = true;
				}
			}
			else
			{
				if($db->add_message(array(
					'sender_id' => fsId(),
					'recip_id' => $fs['id'],
					'unread' => 1,
					'name' => $subject,
					'msg' => $message,
					'time' => date('Y-m-d H:i:s'),
					'attach' => $mail['attach']
				)))
				{
					tplMail(9, $fs['email'],array(
						'name' => $fs['name'],
						'sender' => $sender['name'],
						'anrede' => genderWord($sender['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
						'link' => BASE_URL.'/?page=message&amp;conv='.(int)fsId()
					));
					$check = true;
				}
			}
			
			if(!$check)
			{
				$db->setEmailStatus($mail['id'], $fs['id'], 3);
			}
			else
			{
				$db->setEmailStatus($mail['id'], $fs['id'], 2);
			}
		}
		
		$mails_left = $db->getMailsLeft($mail['id']);
		if($mails_left)
		{
			// throttle to 5 mails per second here to avoid queue bloat
			sleep(2);
		}
		
		return json_encode(array('left' => $mails_left,'status' => 1,'comment'=>'Versende E-Mails ... (aktuelle E-Mail Adresse: '.$fs['email'].')'));
	}
	else
	{
		return 0;
	}
}

function xhr_uploadPhoto($data)
{
	$func = '';

	if(isset($_POST['action']) && $_POST['action'] == 'upload')
	{	
		$id = strip_tags($_POST['pic_id']);
		if(isset($_FILES['uploadpic']))
		{
			$error = 0;
			$datei = $_FILES["uploadpic"]["tmp_name"];
			$datein = $_FILES["uploadpic"]["name"];
			$datein = strtolower($datein);
			$datein = str_replace('.jpeg', '.jpg', $datein);
			$dateiendung = strtolower(substr($datein, strlen($datein)-4, 4));
			if(is_allowed($_FILES["uploadpic"]))
			{
				try 
				{
					$file = makeUnique().$dateiendung;
					move_uploaded_file($datei, './tmp/'.$file);
					$image = new fImage('./tmp/'.$file);
					$image->resize(550, 0);
					$image->saveChanges();
					
				} catch (Exception $e) {
					$func = 'parent.pic_error(\'Deine Datei schein nicht in Ordnung zu sein, nimm am besten ein normales jpg Bild\',\''.$id.'\');';
				}
				

				$func = 'parent.fotoupload(\''.$file.'\',\''.$id.'\');';
			}
			else
			{
				$func = 'parent.pic_error(\'Deine Datei schein nicht in Ordnung zu sein, nimm am besten ein normales jpg Bild\',\''.$id.'\');';
			}
		}
	}
	elseif(isset($_POST['action']) && $_POST['action'] == 'crop')
	{
		global $db;
		$file = str_replace('/', '', $_POST['file']);
		if($img = cropImage($file, $_POST['x'], $_POST['y'], $_POST['w'], $_POST['h']))
		{
			$id = strip_tags($_POST['pic_id']);
			
			@unlink('images/'.$file);
			@unlink('images/crop_'.$file);
			@unlink('images/thumb_crop_'.$file);
			
			copy('tmp/'.$file, 'images/'.$file);
			copy('tmp/crop_'.$file, 'images/crop_'.$file);
			copy('tmp/thumb_crop_'.$file, 'images/thumb_crop_'.$file);
			
			@unlink('tmp/'.$file);
			@unlink('tmp/crop_'.$file);
			@unlink('tmp/thumb_crop_'.$file);
			
			makeThumbs($file);
			
			$db->updatePhoto(fsId(), $file);
			
			$func = 'parent.picFinish(\''.$img.'\',\''.$id.'\');';
		}
		else
		{
			$func = 'alert(\'Es ist ein Fehler aufgetreten, Sorry, probiers nochmal\');';
		}
	}
	
	echo '<html>
	<head><title>Upload</title></head><body onload="'.$func.'"></body>
	</html>';
}

function xhr_update_bezirk($data)
{
	global $db;
	return json_encode($db->update('
		UPDATE `'.PREFIX.'bezirk`
		SET 	`email` = '.$db->strval($data['email']).', 
				`email_pass` = '.$db->strval($data['email_pass']).' 
			
				WHERE 	`id` = '.$db->intval($data['bezirk_id']).'
		'));
}

function xhr_update_abholer($data)
{
	global $db;
	if($db->update('
				UPDATE 	`'.PREFIX.'abholen`
				SET 	`foodsaver_id` = '.$db->intval($data['foodsaver']).' 
				WHERE 	`betrieb_id` = '.$db->intval($data['bbid']).'
				AND 	`dow` = '.$db->intval($data['bbdow']).'
	'))
	{
		return json_encode(array('status' => 1));
	}
	else
	{
		return json_encode(array('status' => 0));
	}
}

function xhr_update_abholen($data)
{
	global $db;
	
	if($db->isVerantwortlich($data['bid']) || isBotschafter())
	{
		$db->del('DELETE FROM 	`'.PREFIX.'abholzeiten` WHERE `betrieb_id` = '.$db->intval($data['bid']));
		
		if(is_array($data['newfetchtime']))
		{
			/*
			 * bid	11
			f	update_abholen
			id	11
			newfetchtime[]	1
			newfetchtime[]	2
			newfetchtime[]	0
			nft-count[]	2
			nft-count[]	2
			nft-count[]	2
			nfttime[hour][]	20
			nfttime[hour][]	20
			nfttime[hour][]	20
			nfttime[min][]	0
			nfttime[min][]	0
			nfttime[min][]	0
			 */
			for($i=0;$i<(count($data['newfetchtime'])-1);$i++)
			{
				$db->sql('
				REPLACE INTO 	`'.PREFIX.'abholzeiten`
				(
						`betrieb_id`,
						`dow`,
						`time`,
						`fetcher`
				)
				VALUES
				(
					'.$db->intval($data['bid']).',
					'.$db->intval($data['newfetchtime'][$i]).',
					'.$db->strval(preZero($data['nfttime']['hour'][$i]).':'.preZero($data['nfttime']['min'][$i]).':00').',
					'.$db->intval($data['nft-count'][$i]).'
				)
			');
			}
		}
		/*
		foreach ($data['dow'] as $dow)
		{
			$db->sql('
				REPLACE INTO 	`'.PREFIX.'abholzeiten`		
				(
						`betrieb_id`,
						`dow`,
						`time`,
						`fetcher`
				)
				VALUES
				(
					'.$db->intval($data['bid']).',
					'.$db->intval($dow).',
					'.$db->strval(preZero($data['day'.$dow]['hour']).':'.preZero($data['day'.$dow]['min']).':00').',
					'.$db->intval($data['fetcher'.$dow]).'
				)
			');
		}
		*/
		$betrieb = $db->getVal('name', 'betrieb', $data['bid']);
		$db->addGlocke(explode(',',$data['team']), 'Die Abholzeiten wurden geändert!',$betrieb,'/?page=fsbetrieb&id='.(int)$data['bid']);
		
		return json_encode(array('status' => 1));
	}

	
	/*
	return $db->update('
		UPDATE `abholen`
		SET 	`f` = $db->strval('.$data['f'].'),
			`dow` = $db->strval('.$data['dow'].'),
			`dow1` = $db->strval('.$data['dow1'].'),
			`dow2` = $db->strval('.$data['dow2'].'),
			`dow3` = $db->strval('.$data['dow3'].'),
			`dow4` = $db->strval('.$data['dow4'].'),
			`dow5` = $db->strval('.$data['dow5'].'),
			`dow6` = $db->strval('.$data['dow6'].'),
			`dow0` = $db->strval('.$data['dow0'].'),
		
				WHERE 	`id` = '.$this->intval().'
		');
		*/
}

function xhr_bezirkTree($data)
{
	global $db;
	if($bezirk = $db->getBezirkByParent($data['p']))
	{
		$out = array();
		foreach ($bezirk as $b)
		{
			$hc = false;
			if($b['has_children'] == 1)
			{
				$hc = true;
			}
			$out[] = array(
					'title' => $b['name'],
					'isLazy' => $hc,
					'isFolder' => $hc,
					'ident' => $b['id'],
					'type' => $b['type']
			);
		}
	}
	else
	{
		$out = array('status' => 0);
	}

	return json_encode($out);
}

function xhr_bteamstatus($data)
{
	
	$allow = array(
		0 => true,
		1 => true,
		2 => true
	);
	global $db;
	if($db->isVerantwortlich($_GET['bid']) && isset($allow[(int)$_GET['s']]))
	{
		return $db->update('
			UPDATE `'.PREFIX.'betrieb`
			SET 	`team_status` = '.(int)$_GET['s'].'
			WHERE 	`id` = '.(int)$_GET['bid'].'
		');
	}
	
	
}

function xhr_getBezirk($data)
{
	global $db;
	global $g_data;
	
	$g_data = $db->getOne_bezirk($data['id']);
	
	$g_data['mailbox_name'] = '';
	if($mbname = $db->getMailboxname($g_data['mailbox_id']))
	{
		$g_data['mailbox_name'] = $mbname;
	}
	
	$foodsaver_values = $db->getBasics_foodsaver($data['id']);
	
	$out = array();
	$out['status'] = 1;
	$id = id('botschafter');
	
	$inputs = '<input type="text" name="'.$id.'[]" value="" class="tag input text value" />';
	if(!empty($g_data['foodsaver']))
	{
		$inputs = '';
		if(isset($g_data['botschafter']) && is_array($g_data['botschafter']))
		{
			foreach ($g_data['botschafter'] as $fs)
			{
				$inputs .= '<input type="text" name="'.$id.'['.$fs['id'].'-a]" value="'.$fs['name'].'" class="tag input text value" />';
			}
		}
	}
	
	$inputs = '<div id="'.$id.'">'.$inputs.'</div>';
	
	$cats = $db->getBasics_bezirk();
	$out['html'] = v_form('bezirkForm', array(
		v_form_hidden('bezirk_id', (int)$data['id']),
		v_form_select('parent_id',array('values'=>$cats)),
		v_form_select('master',array('label'=> 'Master Bezirk','desc'=>'Alle Foodsaver sind automatisch mit im Master Bezirk sofern einer angegeben wurde','values'=>$cats)),
		v_form_text('name'),
		v_form_text('mailbox_name',array('desc'=>'Achtung! nicht willkürlich ändern, auch darauf achten das unter Mailboxen die Mailbox noch nciht existiert.')),
		v_form_text('email_name',array('label'=>'Absender-Name')),
		v_form_select('type',array('label'=>'Bezirks-Typ','values' => array(
			array('id' => '1', 'name' => 'Stadt'),
			array('id' => '8', 'name' => 'Großstadt (ohne Anmeldemöglichkeit)'),
			array('id' => '9', 'name' => 'Stadtteil'),
			array('id' => '2', 'name' => 'Bezirk'),
			array('id' => '3', 'name' => 'Region'),
			array('id' => '5', 'name' => 'Bundesland'),
			array('id' => '6', 'name' => 'Land'),
			array('id' => '7', 'name' => 'Orgateam')
		))),
		v_input_wrapper(s($id), $inputs,$id)
	),array('submit' => s('save'))).
	v_input_wrapper('Master-Update', '<a class="button" href="#" class="button" onclick="if(confirm(\'Master-Update wirklich starten?\')){ajreq(\'masterupdate\',{app:\'geoclean\',id:'.(int)$data['id'].'});}return false;">Master-Update Starten</a>','masterupdate',array('desc'=>'Bei allen Kind Regionen '.$g_data['name'].' als Master eintragen'));
	
	$out['script'] = '
		$("#bezirkform-form").unbind("submit");	
		$("#bezirkform-form").submit(function(ev){
			ev.preventDefault();
			
			$("#dialog-confirm-msg").html("Sicher das Du die &Auml;nderungen am Bezirk speichern m&ouml;chtest?");
			
			$( "#dialog-confirm" ).dialog("option","buttons",{
					"Ja, Speichern": function() 
					{
						showLoader();
						$.ajax({
							url: "xhr.php?f=saveBezirk",
							data: $("#bezirkform-form").serialize(),
							dataType: "json",
							success: function(data) {
								$("#info-msg").html("");
								$.globalEval(data.script);
								$( "#dialog-confirm" ).dialog( "close" );
								$("#tree").dynatree("getTree").reload();
							},
							complete: function(){
								hideLoader();
							}
						});
					},
					"Nein, doch nicht": function()
					{
						$( "#dialog-confirm" ).dialog( "close" );
					}
				});
			
			$("#dialog-confirm").dialog("open");
			
		});
			
		$("input[type=\'submit\']").button();
			
		$("#'.$id.' input").tagedit({
			autocompleteURL: "xhr.php?f=getRecip",
			allowEdit: false,
			allowAdd: false
		});	
					
		$(window).keydown(function(event){
		    if(event.keyCode == 13) {
		      event.preventDefault();
		      return false;
		    }
		  });		
	';
	
	if($foodsaver = $db->getFsMap($data['id']))
	{
		$out['foodsaver'] = $foodsaver;
	}
	if($betriebe = $db->getMapsBetriebe($data['id']))
	{
		$out['betriebe'] = $betriebe;
		foreach ($out['betriebe'] as $i => $b)
		{
			$img = '';
			if($b['kette_id'] != 0)
			{
				if($img = $db->getVal('logo', 'kette', $b['kette_id']))
				{
					$img = '<a href="/?page=betrieb&id='.(int)$b['id'].'"><img style="float:right;margin-left:10px;" src="'.idimg($img,100).'" /></a>';
				}
			}
			$button = '';
			if($db->isInTeam($b['id']))
			{
				$button = '<div style="text-align:center;padding:top:8px;"><span onclick="goTo(\'/?page=fsbetrieb&id='.(int)$b['id'].'\');" class="bigbutton cardbutton ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Zur Teamseite</span></span></div>';
			}
			else
			{
				$button = '<div style="text-align:center;padding:top:8px;"><span onclick="betriebRequest('.(int)$b['id'].');" class="bigbutton cardbutton ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><div style="text-align:center;padding:top:8px;"><span class="ui-button-text">Ich möchte hier Essen abholen</span></span></div>';
			}
			
			$verantwortlicher = '';
			if($v = $db->getTeamleader($b['id']))
			{
				$verantwortlicher = '<p><a href="#" onclick="profile('.(int)$b['id'].');return false;"><img src="'.img().'" /></a><a href="#" onclick="profile('.(int)$b['id'].');return false;">'.$v['name'].'</a> ist verantwortlich</p>';
			}
			
			$out['betriebe'][$i]['bubble'] = '<div style="height:110px;overflow:hidden;width:270px;"><div style="margin-right:5px;float:right;">'.$img.'</div><h1 style="font-size:13px;font-weight:bold;margin-bottom:8px;"><a onclick="betrieb('.(int)$b['id'].');return false;" href="#">'.jsSafe($b['name']).'</a></h1><p>'.jsSafe($b['str'].' '.$b['hsnr']).'</p><p>'.jsSafe($b['plz']).' '.jsSafe($b['stadt']).'</p>'.$button.'</div><div style="clear:both;"></div>';
		}
	}
	
	return json_encode($out);
}

function xhr_acceptBezirkRequest($data)
{
	global $db;
	if(isBotFor($data['bid']) || isOrgaTeam())
	{
		$db->acceptBezirkRequest($data['fsid'],$data['bid']);
		
		return json_encode(array('status'=>1));
	}
}

function xhr_denyBezirkRequest($data)
{
	global $db;
	if(isBotFor($data['bid']) || isOrgaTeam())
	{
		$db->denyBezirkRequest($data['fsid'],$data['bid']);
		return json_encode(array('status'=>1));
	}
}

function xhr_denyRequest($data)
{
	global $db;
	if($db->isVerantwortlich($data['bid']))
	{
		$db->denyRequest($data['fsid'],$data['bid']);
		return json_encode(array('status'=>1));
	}
}

function xhr_signoutBezirk($data)
{
	$db = loadModel('bezirk');
	if($db->mayBezirk($data['bid']))
	{
		$db->del('DELETE FROM `'.PREFIX.'foodsaver_has_bezirk` WHERE `bezirk_id` = '.(int)$data['bid'].' AND `foodsaver_id` = '.(int)fsId().' ');
		$db->del('DELETE FROM `'.PREFIX.'botschafter` WHERE `bezirk_id` = '.(int)$data['bid'].' AND `foodsaver_id` = '.(int)fsId().' ');
		return 1;
	}
	
	return 0;
}

function xhr_acceptRequest($data)
{
	global $db;
	if($db->isVerantwortlich($data['bid']) || isBotschafter())
	{
		$db->acceptRequest($data['fsid'],$data['bid']);
		
		$db->add_betrieb_notiz(array(
				'foodsaver_id' =>$data['fsid'],
				'betrieb_id' => $data['bid'],
				'text' => '{ACCEPT_REQUEST}',
				'zeit' => date('Y-m-d H:i:s'),
				'milestone' => 2
		));
		
		$bezirk_id = $db->getVal('bezirk_id', 'betrieb', $data['bid']);
		$db->linkBezirk($data['fsid'], $bezirk_id);
		
		return json_encode(array('status'=>1));
	}
}

function xhr_warteRequest($data)
{
	global $db;
	if($db->isVerantwortlich($data['bid']) || isBotschafter() || isOrgaTeam())
	{
		$db->warteRequest($data['fsid'],$data['bid']);
		
		return json_encode(array('status'=>1));
	}
}

function xhr_betriebRequest($data)
{	
	$status = 1;
	$msg = 'Hallo Welt';
	
	global $db;
	$foodsaver = $db->getVal('name', 'foodsaver', fsId());
	$betrieb = $db->getVal('name', 'betrieb', $data['id']);
	$bezirk_id = $db->getVal('bezirk_id', 'betrieb', $data['id']);
	if($fsid = $db->getVerantwortlicher($data['id']))
	{
		$msg = 'Der Verartwortliche wurde über Deine Anfrage informiert und wird sich bei Dir melden!';
		
		$biebs = $db->getBiebsForStore($data['id']);

		$model = loadModel('betrieb');
		$model->addBell($biebs, 'store_new_request_title', 'store_new_request', 'img img-store brown', array(
				'href' => '/?page=fsbetrieb&id='.(int)$data['id']
			), array(
				'user' => S::user('name'),
				'name' => $betrieb
			), 'store-request-'.(int)$data['id']);
	}
	else
	{
		$msg = 'Für Diesen Betrieb gibt es noch keinen verantwortlichen, Der Botschafter wurde informiert';
		
		
		$botsch = array();
		$add = '';
		if($b = $db->getBotschafter($bezirk_id))
		{
			foreach ($b as $bb)
			{
				$botsch[] = $bb['id'];
			}
			
		}
		else 
		{
			$botsch = $db->getOrgateam();
			$add = ' Es gibt aber keinen Botschafter';
		}
		
		$db->addGlocke($botsch, $foodsaver.' möchte ins Team!'.$add,$betrieb,'/?page=fsbetrieb&id='.(int)$data['id'].'&request='.(int)fsId());
	}
	
	$db->teamRequest(fsId(), $data['id']);
	
	return json_encode(array('status' => $status,'msg' => $msg));
}

function xhr_getBezirkChildren($data)
{
	global $db;
	if($bezirke = $db->getBezirkByParent($data['p']))
	{
		$region_html = '
		<div>
			<select name="bezirk_id" onchange="nextChildren(this);">';
		foreach ($bezirke as $b)
		{
			$region_html .= '
					<option value="'.$b['id'].'">'.$b['name'].'</option>';
		}
		
		$region_html .= '
			</select>
		</div>';
		
		return json_encode(array(
			'html' => $region_html,
			'status' => 1
		));
		
	}
	else 
	{
		return json_encode(array('status' => 0));
	}
}

function xhr_checkNewBezirk()
{
	global $db;
	$new = $db->qRow('
			SELECT `new_bezirk`,`bezirk_id`
			FROM `'.PREFIX.'foodsaver`
			WHERE `id` = '.(int)fsId().'
	');
	
	$new['botschafter'] = $db->getBotschafter($new['bezirk_id']);
	
	print_r($new);
	
	if(!empty($new))
	{
		return json_encode(array(
			'status' => 0,
			'data' => $new
		));
	}
	else
	{
		return json_encode(array(
			'status' => 1
		));
	}
}

function xhr_myRegion($data)
{
	global $db;
	$bezirk_id = (int)$data['b'];
	$new = strip_tags($data['new']);
	$new = trim($new);
	$want_new = 0;
	if(!empty($new))
	{
		$want_new = 1;
	}
	if($bezirk_id > 0)
	{
		
		$db->update('
			UPDATE `'.PREFIX.'foodsaver`
			SET 	`bezirk_id` = '.(int)$bezirk_id.',
					`new_bezirk` = '.$db->strval($new).',
					`want_new` = '.$want_new.'
			WHERE 	`id` = '.(int)fsId().'		
		');
		
		$db->relogin();
		
		$fs = $db->getOne_foodsaver(fsId());
		
		tplMail(5, $fs['email'],array(
			'anrede' => genderWord($fs['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
			'name' => $fs['name'].' '.$fs['nachname']
		));
		
		return json_encode(array(
			'status' => 1,
			'msg' => s('edit_success')
		));
	}
	else
	{
		return json_encode(array(
				'status' => 0,
				'msg' => s('error_default')
		));
	}
}

function xhr_addFaq_kategorie_id($data)
{
	if(isOrgaTeam())
	{
		global $db;
	
		$name = trim($data['neu']);
		$name = strip_tags($name);

		if(!empty($name))
		{
			if($id = $db->insert('INSERT INTO '.PREFIX.'faq_category(`name`)VALUES('.$db->strval($name).')'))
			{
				return json_encode(array('id'=>$id,'name'=>$name));
			}
		}
	}
	return 0;
}

function xhr_checkmail($data)
{
	if(validEmail($data['email']))
	{
		global $db;
		if($id = $db->qOne('SELECT `id` FROM `'.PREFIX.'foodsaver` WHERE `email` = '.$db->strval($data['email'])))
		{
			if((int)$id > 0)
			{
				return json_encode(array('status'=>0));
			}
		}
		return json_encode(array('status'=>1));
	}
	
	return json_encode(array('status'=>0));
}

function xhr_saveBezirk($data)
{
	global $g_data;
	global $db;
	$g_data = $data;
	
	$mbid = (int)$db->qOne('SELECT mailbox_id FROM '.PREFIX.'bezirk WHERE id = '.(int)$data['bezirk_id']);
	
	if(strlen($g_data['mailbox_name']) > 1)
	{
		if($mbid > 0)
		{
			$db->update('UPDATE '.PREFIX.'mailbox SET name = '.$db->strval($g_data['mailbox_name']).' WHERE id = '.(int)$mbid);
		}
		else 
		{
			$mbid = $db->insert('INSERT INTO '.PREFIX.'mailbox(`name`)VALUES('.$db->strval($g_data['mailbox_name']).')');
			$db->update('UPDATE '.PREFIX.'bezirk SET mailbox_id = '.(int)$mbid.' WHERE id = '.(int)$data['bezirk_id']);
		}
	}
	
	$botschafter = handleTagselect('botschafter');
	
	$db->update_bezirkNew($data['bezirk_id'], $g_data);
	
	addXhJs('pulseInfo("'.s('edit_success').'");');
	
	return xhr_out();
}

function xhr_addFetcher($data)
{
	global $db;
	if(($db->isInTeam($data['bid']) || isBotschafter() || isOrgaTeam()) && isVerified())
	{
		/*
		 * 	[f] => addFetcher
		    [date] => 2013-09-23 20:00:00
		    [bid] => 1
		 */
		
		if(!empty($data['to']))
		{
			incLang('fsbetrieb');
			if(empty($data['from']))
			{
				$data['from'] = date('Y-m-d');
			}
			$time = explode(' ', $data['date']);
			$time = $time[1];
			
			$from = strtotime($data['from']);
			$to = strtotime($data['to']);
			if($to > time() + 86400*7*3)
			{
				info('Das Datum liegt zu weit in der Zukunft!');
				return 0;
			}
			
			$start = strtotime($data['date']);
			
			$cur_date = $from;
			
			$dow = date('w',$start);
			$count = 0;
			do 
			{
				if(date('w',$cur_date) == $dow)
				{
					$count++;
					$db->addFetcher(fsId(),$data['bid'],date('Y-m-d',$cur_date).' '.$time);
				}
				if($count > 20)
				{
					break;
				}
				// + 1 Tag
				$cur_date += 86400;
			}
			while ($to > $cur_date);
			info(s('date_add_successful'));
			return '2';
		}
		elseif(!empty($data['from']))
		{
			return 0;
		}
		else
		{
			$data['date'] = date('Y-m-d H:i:s', strtotime($data['date']));
			if($db->addFetcher(fsId(),$data['bid'],$data['date']))
			{
				return img($db->getVal('photo', 'foodsaver', fsId()));
			}
		}
		
		
	}
	
	return '0';
}

function xhr_delDate($data)
{
	global $db;
	if($db->isInTeam($data['bid']))
	{
		$db->delFetchDate($data['date'],$data['bid'],fsId());
		
		if(isset($data['msg']))
		{
			$db->addTeamMessage($data['bid'],$data['msg']);
		}
		
		return json_encode(array(
			'status' => 1
		));
	}
}

function xhr_fetchDeny($data)
{
	global $db;
	if($db->isVerantwortlich($data['bid']) || isOrgaTeam())
	{
		$db->denyFetcher($data['fsid'], $data['bid'], date('Y-m-d H:i:s',strtotime($data['date'])));
		return 1;
	}
}


function xhr_fetchConfirm($data)
{
	global $db;
	if($db->isVerantwortlich($data['bid']) || isOrgaTeam())
	{
		$db->confirmFetcher($data['fsid'], $data['bid'], date('Y-m-d H:i:s',strtotime($data['date'])));
		return 1;
	}
}

function xhr_becomeBezirk($data)
{
	if(may())
	{
		Mem::delPageCache('/?page=dashboard');
		$bezirk_id = (int)$data['b'];
		$new = '';
		if(isset($data['new']))
		{
			$new = preg_replace('/a-zA-ZäöüÄÖÜß\ /', '', $data['new']);
		}
		global $db;
		
		if(empty($new) && $bezirk_id > 0)
		{
			/*
			if(($active = $db->qOne('SELECT `active` FROM `'.PREFIX.'foodsaver_has_bezirk` WHERE `bezirk_id` = '.(int)$bezirk_id.' AND `foodsaver_id` = '.(int)fsId().' ')) !== false)
			{
				if($active == 1)
				{
					return json_encode(array(
							'script' => 'pulseInfo(\''.jsSafe(s('already_in_bezirk')).'\');',
							'status' => 2
					));
				}
			}
			*/
			/*
				// schon im bezirk
				if($active == 1)
				{
					return json_encode(array(
						'script' => 'pulseInfo(\''.jsSafe(s('already_in_bezirk')).'\');',
						'status' => 2
					));
				}
				else
				{
					return json_encode(array(
						'script' => 'pulseInfo(\''.jsSafe(s('request_already_send')).'\');',
						'status' => 2
					));
				}
			}
			else
			{
			*/
				$active = 1;
				$db->insert('
					REPLACE INTO  `'.PREFIX.'foodsaver_has_bezirk` (`bezirk_id`,`foodsaver_id`,`active`)
					VALUES ('.(int)$bezirk_id.','.fsId().', '.$active.' )
				');
					
				if(!getBezirkId())
				{
					$db->update('UPDATE '.PREFIX.'foodsaver SET bezirk_id = '.(int)$bezirk_id.' WHERE id = '.(int)fsId());
				}
				
				if($bots = $db->getBotschafter($bezirk_id))
				{
					$model =loadModel();
					
					if(
						($foodsaver = $db->getValues(array('verified','name','nachname','photo'), 'foodsaver', fsId())) &&
						($bezirk = $db->getValues(array('name'),'bezirk',$bezirk_id))
					)
					{
						if($foodsaver['verified'] == 1)
						{
							$model->addBell(
								$bots,
								'new_foodsaver_title',
								'new_foodsaver_verified',
								img($foodsaver['photo'],50),
								array( 'href' => '#','onclick' => 'profile('.(int)fsId().');return false;'),
								array( 
									'name' => $foodsaver['name'].' '.$foodsaver['nachname'], 
									'bezirk' => $bezirk['name'] 
								),
								'new-fs-'.fsId()
							);
						}
						else
						{
							$model->addBell(
								$bots,
								'new_foodsaver_title',
								'new_foodsaver',
								img($foodsaver['photo'],50),
								array( 'href' => '#','onclick' => 'profile('.(int)fsId().');return false;'),
								array( 
									'name' => $foodsaver['name'].' '.$foodsaver['nachname'], 
									'bezirk' => $bezirk['name'] 
								),
								'new-fs-'.fsId(),
								false
							);
						}
					}
				}
					
				if($botschafter = $db->getBotschafter($bezirk_id))
				{
					return json_encode(array(
						'active' => $active,
						'status' => 1,
						'botschafter' => $botschafter
					));
				}
				else
				{
					return json_encode(array(
						'active' => $active,
						'status' => 1,
						'botschafter' => false
					));
				}
			//}
		}
	}
}

function xhr_delBPost($data)
{
	global $db;
	$fsid = $db->getVal('foodsaver_id', 'betrieb_notiz', $data['pid']);
	if(isOrgaTeam() || $fsid == fsId())
	{
		$db->deleteBPost($data['pid']);
		return 1;
	}
	else
	{
		return 0;
	}
}

function xhr_delPost($data)
{
	$db = loadModel('bezirk');
	
	$fsid = $db->getVal('foodsaver_id', 'theme_post', $data['pid']);
	$bezirkId = $db->getVal('bezirk_id', 'bezirk_has_wallpost', $data['pid']);

	if(isOrgaTeam() || $fsid == fsId() || ($bezirkId && isBotFor($bezirkId)))
	{
		$db->deletePost($data['pid']);
		return 1;
	}
	else 
	{
		return 0;
	}
}

function xhr_abortEmail($data)
{
	global $db;
	
	if(fsId() == $db->getVal('foodsaver_id', 'send_email', $data['id']))
	{
		$db->update('UPDATE '.PREFIX.'email_status SET status = 4 WHERE email_id = '.(int)$data['id']);
	}
	
}

function xhr_bcontext($data)
{
	global $db;
	if($db->isVerantwortlich($data['bid']) || isBotFor($data['bzid']) || isOrgaTeam())
	{
		$check = false;
		if($data['action'] == 'toteam')
		{
			$check = true;
			$db->update('UPDATE `'.PREFIX.'betrieb_team` SET `active` = 1 WHERE foodsaver_id = '.(int)$data['fsid'].' AND betrieb_id = '.(int)$data['bid']);
		}
		else if ($data['action'] == 'tojumper')
		{
			$check = true;
			$db->update('UPDATE `'.PREFIX.'betrieb_team` SET `active` = 2 WHERE foodsaver_id = '.(int)$data['fsid'].' AND betrieb_id = '.(int)$data['bid']);
		}
		else if($data['action'] == 'delete')
		{
			$check = true;
			$db->del('DELETE FROM `'.PREFIX.'betrieb_team` WHERE foodsaver_id = '.(int)$data['fsid'].' AND betrieb_id = '.(int)$data['bid']);
			$db->del('DELETE FROM `'.PREFIX.'abholer` WHERE `betrieb_id` = '.(int)$data['bid'].' AND `foodsaver_id` = '.(int)$data['fsid'].' AND `date` > NOW()');
           
            $msg = loadModel('msg');
           
            if($tcid = $msg->getBetriebConversation((int)$data['bid']))
            {
               $msg->deleteUserFromConversation($tcid, (int)$data['fsid'], true);
            }
            if($scid = $msg->getBetriebConversation((int)$data['bid'], true))
            {
              $msg->deleteUserFromConversation($scid, (int)$data['fsid'], true);
         	}                       
		}
		
		if($check)
		{
			return json_encode(array(
				'status' => 1
			));
		}
	}
}

function xhr_geoip($data)
{
	if(isset($data['lat']))
	{
		$_SESSION['geo'] = array
		(
				'lat' => $data['lat'],
				'lon' => $data['lon'],
				'region' => $data['region'],
				'city' => $data['city'],
				'plz' => $data['plz']
		);
	}
}
