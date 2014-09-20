<?php 
class StatXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new StatModel();
		$this->view = new StatView();

		parent::__construct();
	}
	
	public function deutschlandtour()
	{
		$dialog = new XhrDialog();
		$dialog->setTitle('Lebensmittelretten Tour 2014');
		$dialog->addOpt('width', 450);
		
		$dialog->addContent('
					
		'.v_input_wrapper('Möchtest Du Besuch von uns bekommen?', '<p>
			vom <strong>09. - 27. Juni</strong> werden Wir eine Tour machen auf der Wir möglichst viele Regionen unterstützen werden.</p>
				<p>
					Meldet euch, wenn Ihr noch Startschwierigkeiten in eurer Stadt/Region habt, Ihr euch noch nicht getraut habt auf Kooperationssuche zu gehen oder generelle Fragen habt.
				</p>').
				
			v_form_textarea('deutschlandtour_message',array('desc'=>s('deutschlandtour_desc')))	
			);

		$dialog->addButton('Nein Danke', 'ajreq(\'tournothx\',{app:\'stat\'});$("#'.$dialog->getId().'").dialog("close");');
		$dialog->addButton('Nachricht absenden', "ajreq('detoursend',{app:'stat',msg:$('#deutschlandtour_message').val()});" . '$("#'.$dialog->getId().'").dialog("close");');
		$dialog->addButton('Später', 'ajreq(\'tourlater\',{app:\'stat\'});$("#'.$dialog->getId().'").dialog("close");');
		$return = $dialog->xhrout();
		$return['script'] .= '$("#deutschlandtour_message").autosize();';
		return $return;
	}
	
	public function detoursend()
	{
		if($fs = $this->model->getValues(array('id','name','nachname','email'),'foodsaver',fsid()))
		{
			libmail(array('email'=>$fs['email'],'email_name' => $fs['name']), 'kontakt@prographix.de', 'Deutschland Tour Einladung', 
'Da hat sich jemand gemeldet!
					
Name: '.$fs['name'].' '.$fs['nachname'].'
ID: 	'.(int)fsid().'

Nachricht:
'.nl2br(strip_tags($_GET['msg'])).'
						
');
			libmail(array('email'=>$fs['email'],'email_name' => $fs['name']), 'deutschlandtour2014@lebensmittelretten.de', 'Deutschland Tour Einladung',
					'Da hat sich jemand gemeldet!<br />
						<br />
					Name: '.$fs['name'].' '.$fs['nachname'].'<br />
					ID: 	'.(int)fsid().'<br />
			<br />
					Nachricht:<br />
					'.nl2br(strip_tags($_GET['msg'])).'<br />
			<br />
			');
			
			$this->model->setCache('tour-'.fsid().'', strip_tags($_GET['msg']));
			return array(
				'status' => 1,
				'script' => 'pulseInfo("Danke! Deine Nachricht wurde versendet! Wir melden uns per E-Mail!");'		
			);
		}
		
	}
	
	public function tourlater()
	{
		$_SESSION['tour2014'] = true;
	}
	
	public function tournothx()
	{
		$_SESSION['tour2014'] = true;
		$this->model->setCache('tour-'.fsid(), false);
	}
	
	public function startcalc()
	{
		$dialog = new XhrDialog('Statistik Auswertung');
		
		if(isset($_GET['force']))
		{
			$bezirke = $this->model->getAllBezirke();
		}
		else
		{
			$bezirke = $this->model->getAllBezirkeNotUpdated();
		}
		
		$dialog = new XhrDialogConsole('stat','Statistik Auswertung');
		
		$dialog->xhrLoop($bezirke,array(
				'action' => 'calcBezirk',
				'params' => array('id'),
				'logPrefix' => 'Berechne',
				'logParams' => array('name'),
				'timeout' => 40
		));
		
		return $dialog->xhrout();
		
	}
	
	public function statBetriebUpdate()
	{
		
		if($betriebe = $this->model->getBetriebe())
		{
			$dialog = new XhrDialogConsole('stat','Betrieb Team Statistik');
			
			$dialog->xhrLoop($betriebe,array(
				'action' => 'calcBetrieb',
				'params' => array('id'),
				'logPrefix' => 'Berechne',
				'logParams' => array('name') 
			));

			return $dialog->xhrout();
		}
		
		
	}
	
	public function calcBetrieb()
	{
		$bid = (int)$_GET['id'];
		if($bid > 0)
		{
			$added = $this->model->getVal('added','betrieb',$bid);
			
			if($team = $this->model->getBetriebTeam($bid))
			{
				
				foreach ($team as $fs)
				{
					$newdata = array(
						'stat_first_fetch' => $fs['stat_first_fetch'],
						'stat_add_date' => $fs['stat_add_date'],
						'foodsaver_id' => $fs['foodsaver_id'],
						'betrieb_id' => $bid,
						'verantwortlich' => $fs['verantwortlich'],
						'stat_fetchcount' => $fs['stat_fetchcount'],
						'stat_last_fetch' => $fs['stat_last_fetch']
					);
					
					/* first_fetch */
					if($first_fetch = $this->model->getFirstFetchInBetrieb($bid,$fs['foodsaver_id']))
					{
						if((int)$fs['first_fetch_ts'] == 0)
						{
							$newdata['stat_first_fetch'] = $first_fetch;
						}
						if((int)$fs['add_date_ts'] == 0)
						{
							$newdata['stat_add_date'] = $first_fetch;
						}
					}
					
					/*last fetch*/
					if($last_fetch = $this->model->getLastFetchInBetrieb($bid, $fs['foodsaver_id']))
					{
						$newdata['stat_last_fetch'] = $last_fetch;
					}
					
					/* add date*/
					if((int)$newdata['stat_add_date'] == 0)
					{
						$newdata['stat_add_date'] = $added;
					}
					
					/*fetchcount*/
					$fetchcount = $this->model->getBetriebFetchCount($bid,$fs['foodsaver_id'],$fs['stat_last_update'],$fs['stat_fetchcount']);
					
					$this->model->updateBetriebStat(
						$bid, // betrieb id
						$fs['foodsaver_id'], // foodsaver_id
						$newdata['stat_add_date'], // add date
						$newdata['stat_first_fetch'], // erste mal abholen
						$fetchcount, // anzahl abholungen
						$newdata['stat_last_fetch']
					);
				}
			}
		}
		
		return array('status' => 1);
	}
	
	public function calcBezirk()
	{
		$bezirk_id = (int)$_GET['id'];
		$last_update = $this->model->getVal('stat_last_update','bezirk',$bezirk_id);
		$child_ids = $this->model->getChildBezirke($bezirk_id);
		
		/* abholmenge & anzahl abholungen */
		$stat_fetchweight = $this->model->getFetchWeight($bezirk_id,$last_update,$child_ids);
		$stat_fetchcount = $stat_fetchweight['count'];
		$stat_fetchweight = $stat_fetchweight['weight'];

		/* anzahl foodsaver */
		$stat_fscount = $this->model->getFsCount($bezirk_id,$child_ids);
		
		/*anzahl botschafter*/
		$stat_botcount = $this->model->getBotCount($bezirk_id,$child_ids);
		
		/* anzahl posts */
		$stat_postcount = $this->model->getPostCount($bezirk_id,$child_ids);
		
		/* fairteiler_count */
		$stat_fairteilercount = $this->model->getFairteilerCount($bezirk_id,$child_ids);
		
		/* count betriebe */
		$stat_betriebecount = $this->model->getBetriebCount($bezirk_id,$child_ids);
		
		/* count koorp betriebe */
		$stat_betriebCoorpCount = $this->model->getBetriebKoorpCount($bezirk_id,$child_ids);
		
		$this->model->updateStats($bezirk_id, $stat_fetchweight, $stat_fetchcount, $stat_postcount, $stat_betriebecount, $stat_betriebCoorpCount, $stat_botcount, $stat_fscount, $stat_fairteilercount);
		
		return array(
			'status' => 1,
			'fetchweight' => $stat_fetchweight
		);
	}
	
	public function wartung()
	{
		wartung();
		
		$db = loadModel('profile');
		
		/* Master Bezirke */
		if($foodasver = $db->q('
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
				if(!$db->qRow('SELECT bezirk_id FROM `'.PREFIX.'foodsaver_has_bezirk` WHERE foodsaver_id = '.(int)$fs['foodsaver_id'].' AND bezirk_id = '.$fs['master']))
				{
					$db->insert('
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

		/* KG gerettet UPDATE*/
		if($fsids = $db->qCol('SELECT id FROM '.PREFIX.'foodsaver'))
		{
			foreach ($fsids as $fsid)
			{
				$stat_gerettet = $db->getGerettet($fsid);
				$stat_fetchcount = (int)$db->qOne('SELECT COUNT(foodsaver_id) FROM '.PREFIX.'abholer WHERE foodsaver_id = '.(int)$fsid.' AND `date` < NOW()');
				$stat_post = (int)$db->qOne('SELECT COUNT(id) FROM '.PREFIX.'theme_post WHERE foodsaver_id = '.(int)$fsid);
				$stat_post += (int)$db->qOne('SELECT COUNT(id) FROM '.PREFIX.'wallpost WHERE foodsaver_id = '.(int)$fsid);
				$stat_post += (int)$db->qOne('SELECT COUNT(id) FROM '.PREFIX.'betrieb_notiz WHERE foodsaver_id = '.(int)$fsid);
		
				$stat_bananacount = (int)$db->qOne('SELECT COUNT(foodsaver_id) FROM '.PREFIX.'rating WHERE `ratingtype` = 2 AND foodsaver_id = '.(int)$fsid);
		
				$stat_buddycount = (int)$db->qone('SELECT COUNT(foodsaver_id) FROM '.PREFIX.'buddy WHERE foodsaver_id = '.(int)$fsid.' AND confirmed = 1');
		
				$stat_fetchrate = 100;
		
				$count_not_fetch = (int)$db->qOne('SELECT COUNT(foodsaver_id) FROM '.PREFIX.'rating WHERE `ratingtype` = 3 AND foodsaver_id = '.(int)$fsid);
		
				if($count_not_fetch > 0 && $stat_fetchcount >= $count_not_fetch)
				{
					$stat_fetchrate =  round(100-($count_not_fetch / ($stat_fetchcount/100)),2);
				}
		
				$db->update('
						UPDATE '.PREFIX.'foodsaver
		
						SET 	stat_fetchweight = '.$db->floatval($stat_gerettet).',
						stat_fetchcount = '.$db->intval($stat_fetchcount).',
						stat_postcount = '.$db->intval($stat_post).',
						stat_buddycount = '.$db->intval($stat_buddycount).',
						stat_bananacount = '.$db->intval($stat_bananacount).',
						stat_fetchrate = '.$db->floatval($stat_fetchrate).'
		
						WHERE 	id = '.$db->intval($fsid).'
				');
			}
		}
	}
	
	public function cronjobs()
	{
		cronjobs();
	}
}