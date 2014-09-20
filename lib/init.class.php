<?php
class FsIntit extends FoodsaverDb
{
	public function __construct($host = 'localhost', $user = 'root', $pass = '', $db = 'foodsaver')
	{
		parent::__construct($host, $user, $pass, $db);
	}
	
	public function createTables()
	{
		include 'lib/creates.php';
	
		foreach ($create as $c)
		{
			//$this->sql($c);
		}
	
		//$this->importRegionen();
	
		//$this->importKennzeichen();
	
		//$this->importFoodsaver();
	}
	
	public function fsDbDate($datestring)
	{
		// 12/19/1989
		$p = explode(' ', $datestring);
		$date = $p[0];

		$time = array();
		
		$date = explode('/', $date);
		
		if(count($date) == 3)
		{
			$monat = (int)$date[0];
			$tag = (int)$date[1];
			$jahr = (int)$date[2];
			
			if(count($p) > 1)
			{
				$time = $p[1];
				
				$time = explode(':', $time);
				$std = (int)$time[0];
				$min = (int)$time[1];
				$sek = 0;
				if(count($time) > 2)
				{
					$sek = (int)$time[2];
				}
			}
			else
			{
				$std = 0;
				$min = 0;
				$sek = 0;
			}
		}
		
		
		$ts = mktime($std,$min,$sek,$monat,$tag,$jahr);
	
		return date('Y-m-d',$ts);
	}
	
	public function si($str)
	{
		return trim($str);
	}
	
	public function updateCologne()
	{
		$plz = array("50667" => true, "50668" => true, "50670" => true, "50672" => true, "50674" => true, "50676" => true, "50677" => true, "50678" => true, "50679" => true, "50733" => true, "50735" => true, "50737" => true, "50739" => true, "50765" => true, "50767" => true, "50769" => true, "50823" => true, "50825" => true, "50827" => true, "50829" => true, "50858" => true, "50859" => true, "50931" => true, "50933" => true, "50935" => true, "50937" => true, "50939" => true, "50968" => true, "50969" => true, "50996" => true, "50997" => true, "50999" => true, "51061" => true, "51063" => true, "51065" => true, "51067" => true, "51069" => true, "51103" => true, "51105" => true, "51107" => true, "51109" => true, "51143" => true, "51145" => true, "51147" => true, "51149");
		$foodsaver = $this->get_foodsaver();
		$bezirkid = $this->qOne('SELECT id FROM '.PREFIX.'bezirk WHERE name LIKE "Köln" ');
		echo $bezirkid.'<br />';
		
		foreach ($foodsaver as $fs)
		{
			if(isset($plz[$fs['plz']]))
			{
				echo '<p>'.$fs['name'].' '.$fs['nachname'].'</p>';
				$this->update('UPDATE '.PREFIX.'foodsaver SET bezirk_id = '.(int)$bezirkid.' WHERE id = '.(int)$fs['id']);
			}
		}
		
	}
	
	public function importPlz()
	{
		$plz_koeln = array("50667", "50668", "50670", "50672", "50674", "50676", "50677", "50678", "50679", "50733", "50735", "50737", "50739", "50765", "50767", "50769", "50823", "50825", "50827", "50829", "50858", "50859", "50931", "50933", "50935", "50937", "50939", "50968", "50969", "50996", "50997", "50999", "51061", "51063", "51065", "51067", "51069", "51103", "51105", "51107", "51109", "51143", "51145", "51147", "51149");
	
		
	}
	
	public function importBioCompany()
	{
		require_once 'lib/SpreadsheetReader/SpreadsheetReaderFactory.php';
		
		$fp = './data/bc.ods';
		$reader = SpreadsheetReaderFactory::reader($fp);
		$sheets = $reader->read($fp);
		
		$bc = $sheets[0];
		
		print_r($bc);
		
		/*
		[0] => Array
        (
            [0] => Bezirk
            [1] => Adresse
            [2] => PLZ
            [3] => Telefon
            [4] => Mail
            [5] => FilialleiterIn
            [6] => StellvertreterIn
            [7] => Foodsaver Verantwortliche
            [8] => Foodsaver Handy
            [9] => Foodsaver Mail
            [10] => aktive Foodsaver
            [11] => Wann wird abgeholt
            [12] => Umfang
            [13] => Wird noch Hilfe benÃ¶tigt?
            [14] => Nachfragen/Kommentare von BC
            [15] => Bemerkungen der Foodsaver
            [16] => Dabei seid
            [17] => Sticker vorhanden
            [18] => Darf Ã¶ffentlich gemacht werden
            [19] => Darf Name + Nr. von Verantwortlich Foodsaver Ã¶ffentlich
        )

    [1] => Array
        (
            [0] => Charlottenburg
            [1] => ReichsstraÃŸe 10
            [2] => 14052
            [3] => 030/ 810 33 26 20
            [4] => bc-reichsstrasse@biocompany.de
            [5] => Matthias Steiner
            [6] => 
            [7] => Marlene Troch
            [8] => 0151 50 726 725
            [9] => m.troch@gmx.de
            [10] => AndrÃ© Troch 
andre.troch@gmx.net
            [11] => Mi-Sa wird um 11 Uhr durch Foodsaver abgeholt + Sa Abend Backwaren nur bis Oktober /anderen Tage hollt Tafel ab
            [12] => 
            [13] => 
            [14] => Werden noch weitere FS benÃ¶tigt?
        )
		 */
		foreach ($bc as $b)
		{
			$data = array();
			
			$bezirk_id = $this->getBezirkId($b[0]);
			
			$name = explode(' ',trim($b[1]));
			$name = $name[0];
			$data['betrieb_status_id'] = 3;
			$data['bezirk_id'] = $bezirk_id;
			$data['plz'] = trim($b[2]);
			$data['kette_id'] = 3;
			$data['betrieb_kategorie_id'] = 1;
			$data['name'] = 'BC '.$name;
			$data['str'] = $b[1];
			$data['hsnr'] = '';
			$data['status_date'] = date('Y-m-d',strtotime($b[16]));
			$data['status'] = 0;
			$data['ansprechpartner'] = $b[5];
			$data['telefon'] = $b[3];
			$data['fax'] = '';
			$data['email'] = $b[4];
			$this->add_betrieb($data);
		}
		
	}
	
	public function updateLatLon()
	{
		$res = $this->q('SELECT id,`anschrift`,`plz` FROM '.PREFIX.'foodsaver WHERE lon = ""');
		
		echo count($res).' Ohne Fahrschein<br />';
		flush();
		foreach ($res as $r)
		{
			if($ll = getLatLon($r['anschrift'], $r['plz']))
			{
				$this->update(' UPDATE '.PREFIX.'foodsaver SET lat = '.$this->strval($ll['lat']).',lon = '.$this->strval($ll['lng']).' WHERE id = '.$this->intval($r['id']).' ');
			}
		}
	}
	
	public function makeBundeslandBezirk()
	{
		$bund = $this->getBasics_bundesland();
		foreach ($bund as $b)
		{
			$this->addOrGet_bezirk($b['name']);
		}
	}
	
	public function importBotschafter()
	{
		require_once 'lib/SpreadsheetReader/SpreadsheetReaderFactory.php';
		
		$spreadsheetsFilePath = './data/botschafter.ods'; //or test.xls, test.csv, etc.
		$reader = SpreadsheetReaderFactory::reader($spreadsheetsFilePath);
		
		$sheets = $reader->read($spreadsheetsFilePath);
		
		$foodsaver = $sheets[0];

		$laterr=0;
		foreach ($foodsaver as $i => $fs)
		{
			if($id = $this->qOne('SELECT id FROM `'.PREFIX.'foodsaver` WHERE email LIKE '.$this->strval($fs[4])))
			{
				
				$bezirk_id = $this->addOrGet_bezirk($fs[5]);
				$this->update('
					UPDATE '.PREFIX.'foodsaver
					SET 
						`about_me_public` = '.$this->strval($fs[11]).',
						`bezirk_id` = '.$this->intval($bezirk_id).'
					WHERE `id` = '.(int)$id.'
				');
				//$this->del('DELETE FROM '.PREFIX.'botschafter WHERE foodsaver_id = '.(int)$id.' ');
				$this->add_botschafter(array(
					'foodsaver_id' => $id,
					'bezirk_id' => $bezirk_id
				));
				
			}
			elseif($id = $this->qOne('SELECT id FROM `'.PREFIX.'foodsaver` WHERE name LIKE '.$this->strval($fs[2]).' AND nachname LIKE '.$this->strval($fs[3]).' '))
			{
				$bezirk_id = $this->addOrGet_bezirk($fs[5]);
				$this->update('
					UPDATE '.PREFIX.'foodsaver
					SET
						`about_me_public` = '.$this->strval($fs[11]).',
						`bezirk_id` = '.$this->intval($bezirk_id).'
					WHERE `id` = '.(int)$id.'
				');
				$this->add_botschafter(array(
						'foodsaver_id' => $id,
						'bezirk_id' => $bezirk_id
				));
				
			}
			else
			{
				print_r($fs);echo 'nicht gematched'."\n\n\n";
			}
			
		}

	}
	
	public function importFoodsaver()
	{
		require_once 'lib/SpreadsheetReader/SpreadsheetReaderFactory.php';
	
		$spreadsheetsFilePath = './data/foodsaver.ods'; //or test.xls, test.csv, etc.
		$reader = SpreadsheetReaderFactory::reader($spreadsheetsFilePath);
	
		$sheets = $reader->read($spreadsheetsFilePath);
	
		$foodsaver = $sheets[0];
		
		$laterr=0;
		foreach ($foodsaver as $i => $fs)
		{
			if($this->emailExsists(trim($fs[13])))
			{
				continue;
			}
			if($i > 0)
			{
				
				$data = array();
				$data['rolle'] = 1;
				if ($fs[17] == 'Foodsaver')
				{
					$data['rolle'] = 2;
				}
				elseif($fs[17] == 'BotschafterIn')
				{
					$data['rolle'] = 3;
				}
				
				$data['anmeldedatum'] = $this->fsDbDate($fs[1]);
				$land = trim($fs[12]);
				$land = strtolower($land);
				if(strpos($land,'de') !== false || $land == 'd')
				{
					$land = 'Deutschland';
				}
				$data['stadt'] = trim($fs[7]);
				$data['land_id'] = $this->addOrGet_land($land);
				$data['plz'] = str_replace(' ', '', $fs[6]);
				$data['stadt'] = trim($fs[7]);
				
				$data['bundesland_id'] = $this->getBundeslandIdByPlz($data['plz']);
				
				$data['bezirk_id'] = 0;
				$data['autokennzeichen_id'] = $this->addOrGet_autokennzeichen($this->si($fs[11]));
				$data['email'] = trim($fs[13]);
				$data['name'] = $this->si($fs[3]);
				$data['nachname'] = $this->si($fs[4]);
				$data['anschrift'] = $this->si($fs[5]);
				$data['telefon'] = $this->si($fs[14]);
				$data['handy'] = $this->si($fs[15]);
				// 19 fehlt..
				
				$gesch = strtolower($fs[2]);
				
				if($gesch == 'mann')
				{
					$gesch = 1;
				}
				elseif ($gesch == 'frau')
				{
					$gesch = 2;
				}
				else
				{
					$gesch = 3;
				}
				
				$data['geschlecht'] = $gesch;
				$data['geb_datum'] = $this->fsDbDate($fs[10]);
				$data['fs_id'] = $this->si($fs[43]);
				$data['radius'] = $this->si($fs[22]);
				$data['kontakte_betriebe'] = $this->si($fs[28]);
				$data['raumlichkeit'] = $this->si($fs[31]);
				$data['fs_international'] = $this->si($fs[35]);
				$data['fs_orga'] = $this->si($fs[36]);
				$data['talente'] = $this->si($fs[37]);
				$data['anbau'] = $this->si($fs[39]);
				$data['timetable'] = $this->si($fs[40]);
				$data['legal_gerettet'] = $this->si($fs[45]);
				$data['motivation'] = $this->si($fs[48]);
				$data['about_me'] = $this->si($fs[49]);
				$data['kommentar'] = $this->si($fs[50]);
				$data['datenschutz'] = 1;
				$data['haftungsausschluss'] = 1;				
								

				//$data['ernaehrung'] = $this->gval($fs[53], 'ernaehrung');
				//$data['lagerraum'] = $this->gval($fs[55], 'lagerraum');
				
				$data['passwd'] = $this->encryptMd5($data['email'], 'EssenRetten2013');
				$data['admin'] = 0;
				$data['photo'] = '';
				
				$data['orgateam'] = 0;
				
				$data['active'] = 1;
				$data['data'] = json_encode(array('from_google' => $fs));
				
				$data['lat'] = '';
				$data['lon'] = '';
				$data['want_new'] = 0;
				$data['new_bezirk'] = '';
				$data['photo_public'] = 1;
				$data['about_me_public'] = 1;
				//$data['rolle'] = 1;
				
				if($latlng = getLatLon($data['anschrift'],$data['plz'],$data['stadt'],true))
				{
					$data['lat'] = $latlng['lat'];
					$data['lon'] = $latlng['lng'];
				}
				else
				{
					$laterr++;
				}
				
				$id = $this->add_foodsaver($data);
	
			}
		}
		
		echo $laterr.' Fehler';
	}
	
	public function getBundeslandIdByPlz($plz)
	{
		if($out = $this->qOne('SELECT bundesland_id FROM '.PREFIX.'plz WHERE plz = '.$this->strval($plz)))
		{
			return $out;
		}
		return 0;
	}
	
	public function gval($str,$table)
	{
		$ex = explode(',', $str);
		$out = array();
		
		$func = 'addOrGet_'.$table;
		
		foreach ($ex as $e)
		{
			$out[] = $this->$func($this->si($e));
		
		}
		
		return $out;
	}
	
	public function importPlzDb()
	{
		$plza = array();
		$fp = fopen('data/DE_NEU.txt', 'r');
		$i= 0;
		while($row = fgetcsv($fp))
		{
			/*
			 * Array
(
    [0] => DE
    [1] => 01968
    [2] => Schipkau HÃ¶rlitz
    [3] => Brandenburg
    [4] => BB
    [5] => 
    [6] => 00
    [7] => Oberspreewald-Lausitz
    [8] => 12066
    [9] => 51.5299
    [10] => 13.9508
    [11] => 
)
			 */
			
			$stadt_id = $this->addOrGet_stadt($row[2]);
			
			$plz = $row[1];
			$bundesland_id = $this->addOrGet_bundesland($row[3]);
			$region_id = $this->addOrGet_geoRegion($row[7]);
			$kennzeichen_id = $this->addOrGet_stadt_kennzeichen($row[4]);
			$lat = $row[9];
			$lon = $row[10];
			
			
			
			$this->add_plz(array(
				'plz' => $plz,
				'stadt_id' => $stadt_id,
				'stadt_kennzeichen_id' => $kennzeichen_id,
				'bundesland_id' => $bundesland_id,
				'geoRegion_id' => $region_id,
				'lat' => $lat,
				'lon' => $lon			

			));
			

			
		}
	}
	
	public function importRegionen()
	{
		$dir = './data/Regionen';
	
		if (is_dir($dir))
		{
			if ($dh = opendir($dir))
			{
				while (($file = readdir($dh)) !== false)
				{
					if(strlen($file) < 5 && strpos($file, '.') !== false)
					{
						continue;
					}
					else if(filetype($dir.'/'.$file) == 'dir')
					{
						$region = $file;
						$parent_id = $this->insert('INSERT INTO `'.PREFIX.'bezirk`(`name`,`has_children`)VALUES('.$this->strval($region).',1)');
	
						if($bezirk_dh = opendir($dir.'/'.$region))
						{
							while (($b_file = readdir($bezirk_dh)) !== false)
							{
								if(strlen($b_file) < 5 && strpos($b_file, '.') !== false)
								{
									continue;
								}
								else
								{
									$b_file = trim($b_file);
	
									$end = explode('.', $b_file);
	
									if(end($end) == 'ods')
									{
										$bezirk = substr($b_file, 0, (strlen($b_file)-4));
										$bezirk_id = $this->insert('INSERT INTO `'.PREFIX.'bezirk`(`name`,`parent_id`)VALUES('.$this->strval($bezirk).','.$this->intval($parent_id).')');
									}
								}
							}
						}
					}
				}
				closedir($dh);
			}
		}
	
	}
	
	public function importKennzeichen()
	{
		$fp = fopen('data/kennzeichen.csv', 'r');
		while($row = fgetcsv($fp,1024,';'))
		{
			$this->add_autokennzeichen(array('name'=>$row[0],'title'=>$row[1]));
		}
	}
}