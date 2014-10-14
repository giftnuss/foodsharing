<?php
class ProfileView extends View
{
	private $foodsaver;
	
	public function setData($data)
	{
		$this->foodsaver = $data;
	}
	
	public function quickprofile($subtitle)
	{
		$tabs = '';
		$tabs_head = '';
		$verify = '';
		if($this->foodsaver['verified'] == 1)
		{
			$verify = '<span class="tooltip verified" title="'.$this->foodsaver['name'].' ist verifiziert">&nbsp;</span>';
		}
		$ginfo = false;
		$infos = array();
		$bot = array();
		if($this->foodsaver['botschafter'])
		{
			
			foreach ($this->foodsaver['botschafter'] as $b)
			{
				$bot[$b['id']] = '<a class="light" href="?page=bezirk&bid='.$b['id'].'&sub=forum">'.$b['name'].'</a>';
			}
			$infos[] = array(
				'name' => $this->foodsaver['name'].' ist Botschafter für',
				'val' => implode(', ', $bot)
			);
		}
		if($this->foodsaver['foodsaver'])
		{
			$fsa = array();
			foreach ($this->foodsaver['foodsaver'] as $b)
			{
				if(!isset($bot[$b['id']]))
				{
					$fsa[] = '<a class="light" href="?page=bezirk&bid='.$b['id'].'&sub=forum">'.$b['name'].'</a>';
				}
			}
			if(!empty($fsa))
			{
				$infos[] = array(
						'name' => $this->foodsaver['name'].' ist Foodsaver für',
						'val' => implode(', ', $fsa)
				);
			}
		}
		if($this->foodsaver['orga'])
		{
			$bot = array();
			foreach ($this->foodsaver['orga'] as $b)
			{
				if(isOrgateam())
				{
					$bot[$b['id']] = '<a class="light" href="?page=bezirk&bid='.$b['id'].'&sub=forum">'.$b['name'].'</a>';
				}
				else
				{
					$bot[$b['id']] = $b['name'];
				}
			}
			$infos[] = array(
					'name' => genderWord($this->foodsaver['geschlecht'], 'Er','Sie', 'Er/Sie').' ist aktiv in den Orgagruppen',
					'val' => implode(', ', $bot)
			);
		}
		
		if(strlen($this->foodsaver['about_me_public']) > 3)
		{
			$infos[] = array(
				'name'=>'Über '.$this->foodsaver['name'],
				'val' => $this->foodsaver['about_me_public']
			);
		
		}
		
		$infos[] = array('name' => '','val' => '<a href="#" onclick="ajreq(\'reportDialog\',{app:\'report\',fsid:'.(int)$this->foodsaver['id'].'});return false;">Verstoß melden</a>');
		
		$fetchweight = '';
		if($this->foodsaver['stat_fetchweight'] > 0)
		{
			$ginfo = true;
			$fetchweight = '
				<span class="item stat_fetchweight">
					<span class="val">'.number_format($this->foodsaver['stat_fetchweight'], 0, ",", ".").'kg</span>
					<span class="name">gerettet</span>
				</span>';
		}
		
		$fetchcount = '';
		if($this->foodsaver['stat_fetchcount'] > 0)
		{
			$ginfo = true;
			$fetchcount = '
				<span class="item stat_fetchcount">
					<span class="val">'.number_format($this->foodsaver['stat_fetchcount'], 0, ",", ".").'x</span>
					<span class="name">abgeholt</span>
				</span>';
		}
		
		$postcount = '';
		if($this->foodsaver['stat_postcount'] > 0)
		{
			$ginfo = true;
			$postcount = '
				<span class="item stat_postcount">
					<span class="val">'.number_format($this->foodsaver['stat_postcount'], 0, ",", ".").'</span>
					<span class="name">Beiträge</span>
				</span>';
		}
		
		$topinfos = array();
		
		
		
		$opt = '';
		if(isOrgaTeam() || isBotschafter())
		{
			$opt .= '<li><a href="?page=foodsaver&a=edit&id='.$this->foodsaver['id'].'">bearbeiten</a></li>';
		}
		
		if($this->foodsaver['buddy'] === -1 && $this->foodsaver['id'] != fsId())
		{
			$name = explode(' ', $this->foodsaver['name']);
			$name = $name[0];
			$opt .= '<li class="buddyRequest"><a onclick="ajreq(\'request\',{app:\'buddy\',id:'.(int)$this->foodsaver['id'].'});return false;" href="#">Ich kenne '.$name.'</a></li>';
		}
		
		if(isAdmin())
		{
			$opt .= '<li><a href="?page=merge&fsid='.$this->foodsaver['id'].'&rurl='.urlencode(getSelf()).'">*verwandeln*</a></li>';
		}
		
		$add_date = new fDate($this->foodsaver['anmeldedatum']);
		$topinfos[] = array(
			'name' => 'Dabei seit',
			'val' => $add_date->format('d.m.Y')
		);
		
		$b_style = '';
		if(!$ginfo)
		{
			$b_style = ' style="top:86px;"';
		}
		$bval = '- noch keine -';
		
		if((int)$this->foodsaver['stat_bananacount'] == 1)
		{
			$bval = '1 Banane';
		}
		elseif((int)$this->foodsaver['stat_bananacount'] > 1)
		{
			$bval = $this->foodsaver['stat_bananacount'].' Bananen';
		}
		
		if((int)$this->foodsaver['bananen'] > 0)
		{
			$tabs_head .= '<li><a href="#ptab-'.(int)$this->foodsaver['id'].'-2">Vertrauensbananen</a></li>';
			
			
			
			$tabs .= '
				<div id="ptab-'.(int)$this->foodsaver['id'].'-2">
					<div class="ui-padding">
						<table class="pintable">
							<tbody>';
			$odd = 'even';
			foreach ($this->foodsaver['bananen'] AS $b)
			{
				if($odd == 'even')
				{
					$odd = 'odd';
				}
				else
				{
					$odd = 'even';
				}
				$tabs .= '
				<tr class="'.$odd.' bpost">
					<td class="img"><a class="tooltip" title="'.$b['name'].'" href="#"><img onclick="profile('.$b['id'].');return false;" src="'.img($b['photo']).'"></a></td>
					<td><span class="msg">'.nl2br($b['msg']).'</span>
					<div class="foot">
						<span class="time">'.niceDate($b['time_ts']).' von '.$b['name'].'</span>
					</div></td>
				</tr>';
			}
			$tabs .= '
						</tbody>
					</table>
				</div>';
		}
		
		if($this->foodsaver['id'] == fsId())
		{
			$topinfos[] = array(
					'name' => 'Vertrauensbananen',
					'val' => $bval.'<span'.$b_style.' class="vouch-banana" title="Das sind Deine Bananen"><span>&nbsp;</span></span>'
			);
		}
		elseif(!$this->foodsaver['bouched'])
		{
			$topinfos[] = array(
				'name' => 'Vertrauensbananen',
				'val' => $bval.'<a'.$b_style.' onclick="addbanana('.$this->foodsaver['id'].');return false;" href="#" title="'.$this->foodsaver['name'].' eine Vertrauensbanane schenken" class="vouch-banana"><span>&nbsp;</span></a>'
			);
		}
		else
		{
			$topinfos[] = array(
					'name' => 'Vertrauensbananen',
					'val' => $bval.'<span'.$b_style.' class="vouch-banana" title="Du hast '.$this->foodsaver['name'].' schon eine Banane geschenkt"><span>&nbsp;</span></span>'
			);
		}
		
		$fetchquote = '';
		if($this->foodsaver['stat_fetchcount'] > 0)
		{
			$topinfos[] = array(
				'name' => 'Abholquote',
				'val' => ''.round($this->foodsaver['stat_fetchrate'],2).' %'
			);
		}
		if($this->foodsaver['stat_buddycount'] > 0)
		{
			$topinfos[] = array(
					'name' => 'Bekannte',
					'val' => $this->foodsaver['name'].' kennen '.$this->foodsaver['stat_buddycount'].' Foodsaver'
			);
		}
		
		if(!empty($this->foodsaver['photo']))
		{
			$photo = '<img src="'.img($this->foodsaver['photo'],130,'q').'" alt="'.$this->foodsaver['name'].' '.$this->foodsaver['nachname'].'" />';
		}
		else
		{
			$photo = '<img src="img/130_q_avatar.png" alt="'.$this->foodsaver['name'].' '.$this->foodsaver['nachname'].'" />';
		}
		
		if(isOrgaTeam())
		{
			$data = array();
			if(!empty($this->foodsaver['data']))
			{
				$data = json_decode($this->foodsaver['data'],true);
			}
			$tabs_head .= '<li><a href="#ptab-'.(int)$this->foodsaver['id'].'-3">Kontaktinfos</a></li>';
			$tabs .= '
				<div id="ptab-'.(int)$this->foodsaver['id'].'-3">
					<div class="ui-padding">
						'.$this->xv_set(array(
							array('name' => 'Anschrift','val'=>$this->foodsaver['anschrift']),
							array('name' => 'PLZ / Ort','val'=>$this->foodsaver['plz'].' '.$this->foodsaver['stadt']),
							array('name' => 'Telefon','val' => $this->foodsaver['telefon'].'<br />'.$this->foodsaver['handy']),
							array('name' => 'E-Mail Adresse', 'val' => '<a href="mailto:'.$this->foodsaver['email'].'">'.$this->foodsaver['email'].'</a>')
						)).'
					</div>
				</div>';
			$tabs_head .= '<li><a href="#ptab-'.(int)$this->foodsaver['id'].'-4">Anmeldedaten</a></li>';
			$tabs .= '
				<div id="ptab-'.(int)$this->foodsaver['id'].'-4">
					<div class="ui-padding">
						<pre style="font-size:12px;">
						<br />'.print_r($data,true).'
						</pre>
					</div>
				</div>';
		}
		
		return '
			<div id="dialog-profile-info">
				<div id="tabs-profile">
			    	<ul>
			      		<li><a href="#ptab-'.(int)$this->foodsaver['id'].'-1">'.$this->foodsaver['name'].'</a></li>
						'.$tabs_head.'
			    	</ul>
			    	<div id="ptab-'.(int)$this->foodsaver['id'].'-1">
						<div class="xv_left">
							<div style="height:130px;">'.$photo.'</div>
							'.$verify.'
							<ul>
								<li><a onclick="chat('.(int)$this->foodsaver['id'].');closeAllDialogs();return false;" href="#">Nachricht schreiben</a></li>
								'.$opt.'
							</ul>
						</div>
						
						<table>
							<tr>
								<td>
									<div class="statdisplay">
										'.$fetchweight.'
										'.$fetchcount.'
										'.$postcount.'
									</div>
								</td>
							</tr>
							<tr>
								<td>
									'.$this->xv_set($topinfos).'
								</td>
							</tr>
						</table>
						<div style="clear:both;"></div>
						'.$this->xv_set($infos).'
						<div style="display:none">
							<input type="hidden" name="profile-rate-id" id="profile-rate-id" value="'.$this->foodsaver['id'].'" />
							<div class="vouch-banana-title">
								'.$this->foodsaver['name'].' eine Vertrauensbanane schenken
							</div>
							<div class="vouch-banana-desc">
								
								Hier kannst Du etwas dazu schreiben, warum Du gerne '.$this->foodsaver['name'].' eine Banane schenken möchtest. Du kannst jedem Foodsaver nur eine Banane schenken!<br />
								Bitte gebe die Vertrauensbanane nur an Foodsaver die Du persönlich kennst und bei denen Du für Zuverlässigkeit, Vertrauen und Engagement gegen die Verschwendung von Lebensmitteln Deine Hand für ins Feuer legen würdest, also Du 100% sicher bist, dass die Verhaltensregeln und die Rechtsvereinbarung ordnungsgemäß eingehalten werden
								<p><strong>Vertrauensbananen können nicht zurückgenommen werden, sei bitte deswegen besonders achtsam wem Du eine schenkst</strong></p>
								<img src="/img/banana.png" style="float:right;" />
							</div>
						</div>
					</div>
					'.$tabs.'
				</div>';
	}
	
	public function xv_set($rows,$title = '')
	{
		$out = '
	<div class="xv_set">
		<h3>'.$title.'</h3>';
		foreach ($rows as $r)
		{
			$out .= '
		<div class="xv_row">
			<span class="xv_label">'.$r['name'].'</span><span class="xv_val">'.$r['val'].'</span>
		</div>';
		}
	
		return $out.'
	</div>';
	}
}