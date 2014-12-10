<?php
class BasketView extends View
{
	
	public function basket($basket,$wallposts,$requests)
	{
		
		$page = new vPage('Essenskorb #'.$basket['id'], '
		<div class="pure-g">
		    <div class="pure-u-1 pure-u-md-1-3">
				'.$this->pageImg($basket['picture']).'	
			</div>
		    <div class="pure-u-1 pure-u-md-2-3">
				<p>'.nl2br($basket['description']).'</p>
			</div>
		</div>
		');
		
		if($wallposts)
		{
			$page->addSection($wallposts,'Pinnwand');
		}
		if(S::may())
		{
			$page->addSectionRight($this->userBox($basket),'AnbieterIn');
			
			if($basket['lat'] != 0 || $basket['lon'] != 0)
			{
				$map = new vMap();
				$map->addMarker($basket['lat'], $basket['lon']);
				
				$map->setDefaultMarker('basket', 'green');
				
				$page->addSectionRight($map->render(),'Wo?');
			}
			
			if($basket['fs_id'] == fsId())
			{
				if($requests)
				{
					$page->addSectionRight($this->requests($requests),count($requests).' Anfragen');
				}
			}
		}
		else
		{
			$page->addSectionRight(v_info('Für Detailierte Infos darfst Du Dich einloggen!','Hinweis!').'<div>
				<a class="button button-big" href="#"onclick="ajreq(\'login\',{app:\'login\'});return false;">Einloggen</a>
			</div>',array('wrapper' => false));
		}		
		
		$page->render();
	}
	
	public function requests($requests)
	{
		$out = '
		<ul class="linklist conversation-list">';
		
		foreach ($requests as $r)
		{
			$out .= '
			<li><a onclick="profile('.(int)$r['fs_id'].');return false;" href="#"><span class="pics"><img width="50" alt="avatar" src="'.img($r['fs_photo']).'"></span><span class="names">'.$r['fs_name'].'</span><span class="msg"></span><span class="time">'.niceDate($r['time_ts']).'</span><span class="clear"></span></a></li>';
		}
		
		$out.= '
		</ul>';
		
		return $out;
	}
	
	public function userBox($basket)
	{
		$request = '';
		
		if($basket['fs_id'] !=fsId())
		{
			$request = '<div><a class="button button-big" href="#" onclick="ajreq(\'request\',{app:\'basket\',id:'.(int)$basket['id'].'});">Essenskorb Anfragen</a>	</div>';
		}
		else
		{
			$request = '<div><a class="button button-big" href="#" onclick="ajreq(\'removeBasket\',{app:\'basket\',id:'.(int)$basket['id'].'});">Essenskorb löschen</a>	</div>';
		}
		
		return $this->fsAvatarList(array(array(
			'id' => $basket['fs_id'],
			'name' => $basket['fs_name'],
			'photo' => $basket['fs_photo'],
			'sleep_status' => $basket['sleep_status']
		)),array('height' => 60,'scroller' => false)) . 
		$request;
		
	}
	
	private function pageImg($img)
	{
		if($img != '')
		{
			return '<img class="basket-img" src="/images/basket/medium-'.$img.'" />';
		}
		else
		{
			return '<img class="basket-img" src="/img/foodloob.gif" />';
		}
	}
	
	public function basketForm($foodsaver)
	{
		global $g_data;
		$g_data['weight'] = '3';
		$g_data['contact_type'] = 1;
		$g_data['tel'] = $foodsaver['telefon'];
		$g_data['handy'] = $foodsaver['handy'];
		
		$out = '';
		
		$out .= v_form_textarea('description',array('maxlength'=>1705));
		
		$values = array(
			array('id' => 0.25, 'name' => '250 g'),
			array('id' => 0.5, 'name' => '500 g'),
			array('id' => 0.75, 'name' => '750 g')
		);
		
		for ($i=1;$i<=10;$i++)
		{
			$values[] = array(
				'id' => $i,
				'name' => number_format($i,2, ",", "."). ' kg'
			);
		}
		
		for ($i=2;$i<=10;$i++)
		{
			$val = ($i*10);
			$values[] = array(
				'id' => $val,
				'name' => number_format($val,2, ",", "."). ' kg'
			);
		}
		
		$out .= v_form_select('weight',array(
			'values' => $values	
		));
		
		$out .= v_form_checkbox('contact_type',array(
			'values' => array(
				array('id' => 1, 'name' => 'Per Nachricht'),
				array('id' => 2, 'name' => 'Per Telefon-Anruf')
			)
		));
		
		$out .= v_form_text('tel');
		$out .= v_form_text('handy');
		
		$out .= v_form_checkbox('food_type',array(
			'values' => array(
				array('id' => 1, 'name' => 'Backwaren'),
				array('id' => 2, 'name' => 'Obst & Gemüse'),
				array('id' => 3, 'name' => 'Molkereiprodukte'),
				array('id' => 4, 'name' => 'Trockenware'),
				array('id' => 5, 'name' => 'Tiefkühlware'),
				array('id' => 6, 'name' => 'Zubreitetete Speisen'),
				array('id' => 7, 'name' => 'Tierfutter')
			)		
		));
		
		$out .= v_form_checkbox('food_art',array(
				'values' => array(
						array('id' => 1, 'name' => 'sind Bio'),
						array('id' => 2, 'name' => 'sind vegetarisch'),
						array('id' => 3, 'name' => 'sind vegan'),
						array('id' => 4, 'name' => 'sind glutenfrei')
				)
		));
		
		return $out;
	}
	
	public function contactMsg($basket)
	{
		return v_form_textarea('contactmessage');
	}
	
	public function contactTitle($basket)
	{
		return '<img src="'.img($basket['fs_photo']).'" style="float:left;margin-right:15px;" />
		<p>'.$basket['fs_name'].' Kontaktieren</p>
		<div style="clear:both;"></div>';
	}
	
	public function contactNumber($basket)
	{		
		
		$out = '';
		$content = '';
		if(!empty($basket['tel']))
		{
			$content .= ('<tr><td>Festnetz: &nbsp;</td><td>'.$basket['tel'].'</td></tr>');
		}
		if(!empty($basket['handy']))
		{
			$content .= ('<tr><td>Handy: &nbsp;</td><td>'.$basket['handy'].'</td></tr>');
		}
		if(!empty($content))
		{
			$out .= v_input_wrapper('Telefonisch kontaktieren', '<table>'.$content.'</table>');
		}
		
		return $out;
	}
	
	public function listUpdates($updates)
	{
		$out = '<li class="title">Anfragen</li>';
		foreach ($updates as $u)
		{
			$fs = array('id' => $u['fs_id'],'name' => $u['fs_name'],'photo' => $u['fs_photo'],'sleep_status' => $u['sleep_status']);
			$out .= '<li><a href="#" onclick="ajreq(\'answer\',{app:\'basket\',id:'.(int)$u['id'].',fid:'.(int)$u['fs_id'].'});return false;"><span class="button close" onclick="ajreq(\'removeRequest\',{app:\'basket\',id:'.(int)$u['id'].',fid:'.(int)$u['fs_id'].'});return false;"><i class="fa fa-close"></i></span><span class="pics">'.avatar($fs,50).'</span><span class="names">Anfrage von '.$u['fs_name'].'</span><span class="msg">'.$u['description'].'</span><span class="time">'.niceDate($u['time_ts']).'</span><span class="clear"></span></a></li>';
		}
		
		return $out;
	}
	
	public function listMyBaskets($baskets)
	{
		$out = '<li class="title">Deine Essenskörbe</li>';
		foreach ($baskets as $b)
		{
			$img = 'img/basket.png';
			if(!empty($b['picture']))
			{
				$img = 'images/basket/50x50-'.$b['picture'];
				if(!file_exists($img))
				{
					try {
						
						copy('images/basket/thumb-' . $b['picture'], 'images/basket/50x50-' . $b['picture']);
						$this->chmod('images/basket/50x50-' . $b['picture'], 777);
						
						$fimg = new fImage('images/basket/50x50-' . $b['picture']);
						$fimg->cropToRatio(1, 1);
						$fimg->resize(50, 50);
						$fimg->saveChanges();
						
					} catch (Exception $e) {
						$img = 'img/basket.png';
					}
				}
				
			}
			
			$reqtext = s('no_requests');
			
			if($b['req_count'] == 1)
			{
				$reqtext = s('one_request');
			}
			elseif ($b['req_count'] > 0)
			{
				$reqtext =sv('req_count',array('count'=> $b['req_count']));
			}
			
			$out .= '<li class="basket-'.(int)$b['id'].'"><a href="/basket/'.(int)$b['id'].'"><span class="button close" onclick="ajreq(\'removeBasket\',{app:\'basket\',id:'.(int)$b['id'].'});return false;"><i class="fa fa-close"></i></span><span class="pics"><img width="50" src="'.$img.'" alt="avatar" /></span><span class="names">'.tt($b['description'],150).'</span><span class="msg">'.$reqtext.'</span><span class="time">'.niceDate($b['time_ts']).'</span><span class="clear"></span></a></li>';
		}
		
		return $out;
	}
	
	private function chmod($file,$mode)
	{
		exec('chmod 777 /var/www/lmr-v1/freiwillige/' . $file);
	}
	
	public function fsBubble($basket)
	{
		$img = '';
		if(!empty($basket['picture']))
		{
			$img = '<div style="width:100%;max-height:200px;overflow:hidden;"><img src="http://media.myfoodsharing.org/de/items/200/'.$basket['picture'].'" /></div>';
		}
	
		return '
		'.$img.'
		'.v_input_wrapper('Beschreibung', nl2br(autolink($basket['description']))).'
		' .
		'<div style="text-align:center;"><a class="fsbutton" href="http://foodsharing.de/essenskoerbe/'.$basket['fsf_id'].'" target="_blank">Essenskorb anfragen auf foodsharing.de</a></div>';
	}
	
	public function bubbleNoUser($basket)
	{
		$img = '';
		if(!empty($basket['picture']))
		{
			$img = '<div style="width:100%;overflow:hidden;"><img src="/images/basket/medium-'.$basket['picture'].'" width="100%" /></div>';
		}
	
		return '
		'.$img.'
		'.v_input_wrapper('Beschreibung', nl2br(autolink($basket['description']))).'
		';
	}
	
	public function bubble($basket)
	{
		$img = '';
		if(!empty($basket['picture']))
		{
			$img = '<div style="width:100%;overflow:hidden;"><img src="/images/basket/medium-'.$basket['picture'].'" width="100%" /></div>';
		}
	
		return '
		'.$img.'
		'.v_input_wrapper('Beschreibung', nl2br(autolink($basket['description']))).'
		';
	}
	
	public function basketInfoList($conversations,$click = 'msg.loadConversation')
	{
		$list = '';
	
		if(!empty($conversations))
		{
			foreach ($conversations as $c)
			{
				$pics = '';
				$names = '';
				if(!empty($c['member']))
				{
					$picwidth = 50;
					$size = 'med';
						
					if(count($c['member']) > 2)
					{
						$picwidth = 25;
						$size = 'mini';
						shuffle($c['member']);
					}
						
					foreach($c['member'] as $m)
					{
						if($m['id'] == fsId())
						{
							continue;
						}
						$pics .= '<img src="'.img($m['photo'],$size).'" width="'.$picwidth.'" />';
						$names .= ', '.$m['name'];
					}
					$names = substr($names, 2);
					$list .= '<li id="convlist-'.$c['id'].'"><a href="#" onclick="'.$click.'('.$c['id'].');return false;"><span class="pics">'.$pics.'</span><span class="names">'.$names.'</span><span class="msg">'.$c['last_message'].'</span><span class="time">'.niceDate($c['last_ts']).'</span><span class="clear"></span></a></li>';
				}
			}
		}
		else
		{
			$list = '<li class="noconv">'.v_info(s('no_conversations')).'</li>';
		}
	
		return $list;
	
	}
}
