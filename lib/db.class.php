<?php
class Mem
{
	public static $cache;
	public static $connected;
	
	public static function connect()
	{
		Mem::$connected = true;
		Mem::$cache = new Memcached();
		Mem::$cache->addServer(MEM_HOST,MEM_PORT);
	}
	
	public static function set($key,$data,$ttl = 0)
	{
		//return apc_store($key,$data,0);
		return Mem::$cache->set($key,$data,$ttl);
	}
	
	public static function get($key)
	{
		//return apc_fetch($key);
		return Mem::$cache->get($key);
	}
	
	public static function flush()
	{
		//apc_clear_cache('user');
		return Mem::$cache->flush();
	}
	
	public static function del($key)
	{
		//apc_delete($key);
		return Mem::$cache->delete($key);
	}
	
	public static function user($id,$key)
	{
		//return Mem::get('user-'.$key.'-'.$id);
		return Mem::get('user-'.$key.'-'.$id);
	}
	
	public static function userSet($id,$key,$value)
	{
		return Mem::set('user-'.$key.'-'.$id, $value);
	}
	
	public static function userAppend($id,$key,$value)
	{
		$out = array();
		if($val = Mem::user($id,$key))
		{
			if(is_array($val))
			{
				$out = $val;
			}
		}
		$out[] = $value;
		return Mem::set('user-'.$key.'-'.$id, $out);
	}
	
	public static function userDel($id,$key)
	{
		return Mem::del('user-'.$key.'-'.$id);
	}
	
	public static function getPageCache()
	{
		global $g_page_cache_suffix;
		return Mem::get('pc-'.$_SERVER['REQUEST_URI'] . ':' . fsId());
	}
	
	public static function setPageCache($page,$ttl)
	{
		return Mem::set('pc-'.$_SERVER['REQUEST_URI'] . ':' . fsId(), $page, $ttl);
	}
	
	/**
	 * Method to check users online status by checking timestamp from memcahce
	 *
	 * @param integer $fs_id
	 * @return boolean
	 */
	public static function userOnline($fs_id)
	{
		if($time = Mem::user($fs_id, 'active'))
		{
			if((time()-$time) < 600)
			{
				
				return true;
			}
		}
		/*
		 * free memcache from userdata
		 */
		Mem::userDel($fs_id, 'lastMailMessage');
		Mem::userDel($fs_id, 'active');
		return false;
	}
	
	/**
	 * update user activity to show user is online
	 */
	public static function userUpdate()
	{
		Mem::userSet(fsId(), 'active', time());
	}
}

Mem::connect();

interface SlaveInterface
{
	public function toArray();
}

$g_dbclean = false;

class Db
{
	private $mysqli;
	private $is_connected;
	private $values;
	
	public function __construct()
	{
		$this->values = array();
		
		global $g_dbclean;
		$this->mysqli = new mysqli();
		$this->mysqli->connect(DB_HOST, DB_USER, DB_PASS, DB_DB);
		$this->sql("SET NAMES 'utf8'");
		$this->sql("SET CHARACTER SET 'utf8'");
					
		$g_dbclean['mysqli'] = $this->mysqli;
	}
	
	public function addPassRequest($email,$mail = true)
	{
		if($fs = $this->qRow('SELECT `id`,`email`,`name`,`geschlecht` FROM `'.PREFIX.'foodsaver` WHERE `email` = '.$this->strval($email)))
		{
			
			$k = uniqid();
			$key = md5($k);
				
			$id = $this->insert('
			REPLACE INTO 	`'.PREFIX.'pass_request`
			(
				`foodsaver_id`,
				`name`,
				`time`
			)
			VALUES
			(
				'.$this->intval($fs['id']).',
				'.$this->strval($key).',
				NOW()
			)');
			
			if($mail)
			{
				$vars = array(
						'link'=>BASE_URL.'/?page=login&sub=passwordReset&k='.$key,
						'name' => $fs['name'],
						'anrede' => genderWord($fs['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r')
				);

				tplMail(10, $fs['email'],$vars);
				return true;
				
			}
			else
			{
				return $key;
			}	
		}
	
		return false;
	}
	
	public function emailExsists($email)
	{
		$count = $this->qOne('SELECT COUNT(`id`) FROM '.PREFIX.'foodsaver WHERE email = '.$this->strval($email));
		$count = (int)$count;
		
		if($count > 0)
		{
			return true;
		}
		
		return false;
	}
	
	public function addComment($data)
	{
		$out = false;
		if(isset($data['name']))
		{
			switch($data['name'])
			{
				case 'betrieb' : $out = $this->addCommentBetrieb($data); break;
				default:return false; break;
			}
		}
		
		if($out !== false)
		{
			return $out;
		}
		else
		{
			return '0';
		}
	}
	
	public function addCommentBetrieb($data)
	{
		if((int)$data['id'] > 0 && strlen($data['comment']) > 0)
		{
			$this->insert('
			INSERT INTO 	`'.PREFIX.'betrieb_notiz`
			(
				`foodsaver_id`,
				`betrieb_id`,
				`text`,
				`zeit`
			) 
			VALUES
			(
				'.$this->getFoodsaverId().',
				'.$this->intval($data['id']).',
				'.$this->strval(urldecode($data['comment'])).',
				'.$this->dateval(date('Y-m-d H:i:s')).'
			)');
			
			return json_encode(array(
				'status' => 1,
				'msg' => 'Notiz wurde gespeichert!'
			));
			
		}
		
		return false;
	}
	
	public function getRolle()
	{
		$out = array();
		if(isset($_SESSION['client']['botschafter']))
		{
			if($name = $this->getBezirkName($_SESSION['client']['botschafter'][0]['bezirk_id']))
			{
				$out['botschafter'] = array
				(
					'bezirk_id' => (int)$_SESSION['client']['botschafter'][0]['bezirk_id'],
					'bezirk_name' => $name
				);
			}
		}
		
		if(isset($_SESSION['client']['verantwortlich']))
		{
			$out['verantwortlich'] = array();
			
			$i=0;
			foreach ($_SESSION['client']['verantwortlich'] as $v)
			{
				if($name = $this->getBetriebName($v['betrieb_id']))
				{
					$out['verantwortlich'][] = array
					(
							'betrieb_id' => (int)$v['betrieb_id'],
							'betrieb_name' => $name
					);
				}
				$i++;
				if($i==1000)
				{
					break;
				}
			}
		}
		
		if(empty($out))
		{
			return false;
		}
		else
		{
			return $out;
		}
	}
	
	
	
	public function getBezirkName($bezirk_id = false)
	{
		if($bezirk_id === false)
		{
			$bezirk_id = $this->getCurrentBezirkId();
		}
		return $this->qOne('SELECT `name` FROM `'.PREFIX.'bezirk` WHERE `id` = '.$this->intval($bezirk_id));
	}
	
	public function getBetriebName($betrieb_id)
	{
		return $this->qOne('SELECT `name` FROM `'.PREFIX.'betrieb` WHERE `id` = '.$this->intval($betrieb_id));
	}
	
	

	public function editBetrieb($data,$id)
	{
		/*
		 * 
		 *     Array
(
    [form_submit] => editbetrieb
    [bezeichnung] => Praxis
    [str] => Bauerbankstraße
    [hsnr] => 9
    [plz] => 50969
    [ort] => Köln
    [kategorie] => 1
    [status] => 2
    [betreibskette] => 4
)
		 */
		
		

		if(true)
		{
			$this->removeAllVerantwortlicher($id);
			
			if(!empty($data['verantwortlicherfoodsaver']))
			{
				$this->addVerantwortlicher($data['verantwortlicherfoodsaver'], $id);
			}
			
			$bezirk_id = $this->getBezirkIdByPlz($plz_id);
			return $this->update('
				UPDATE '.PREFIX.'betrieb
				
				SET	plz = '.$this->strval(trim($data['plz'])).',
					bezirk_id = '.$this->intval($bezirk_id).',
					kette_id = '.$this->intval($data['betreibskette']).',
					betrieb_kategorie_id = '.$this->intval($data['kategorie']).',
					name = '.$this->strval($data['bezeichnung']).',
					str = '.$this->strval($data['str']).',
					hsnr = '.$this->strval($data['hsnr']).',
					`status` = '.$this->intval($data['status']).',
					status_date = '.$this->dateval(date('Y-m-d H:i:s')).',
					ansprechpartner = '.$this->strval($data['ansprechpartner']).', 
					telefon = '.$this->strval($data['telefon']).', 
					email = '.$this->strval($data['emailadresse']).', 
					fax = '.$this->strval($data['fax']).'
			
				WHERE `'.PREFIX.'betrieb`.`id` = '.$this->intval($id).'
		');
		}
	}
	
	public function addBetrieb($data)
	{
		/*
		 * Array
(
    [form_submit] => newbetrieb
    [bezeichnung] => kjl
    [str] => 
    [hsnr] => 
    [plz] => 123
    [ort] => 
    [kategorie] => 2
    [status] => 2
    [betreibskette] => 3
    [ansprechpartner] => 
    [emailadresse] => 
    [telefon] => 
    [fax] => 
    [verantwortlicherfoodsaver] => 
)
		 */
		
		
		
		if($betrieb_id = $this->insert('
				INSERT INTO '.PREFIX.'betrieb
				(
					plz, 
					bezirk_id, 
					kette_id, 
					betrieb_kategorie_id, 
					name, 
					str, 
					hsnr, 
					`status`, 
					status_date,
					ansprechpartner, 
					telefon, 
					email, 
					fax
				)
				VALUES
				(
					'.$this->strval($data['plz']).',
					'.$this->intval($data['bezirk_id']).',
					'.$this->intval($data['betreibskette']).',
					'.$this->intval($data['kategorie']).',
					'.$this->strval($data['bezeichnung']).',
					'.$this->strval($data['str']).',
					'.$this->strval($data['hsnr']).',
					'.$this->intval($data['status']).',
					'.$this->dateval(date('Y-m-d')).',
					'.$this->strval($data['ansprechpartner']).',
					'.$this->strval($data['telefon']).',
					'.$this->strval($data['emailadresse']).',
					'.$this->strval($data['fax']).'
					
				)
		'))
		{
			if(!empty($data['verantwortlicherfoodsaver']))
			{
				$this->addVerantwortlicher($data['verantwortlicherfoodsaver'],$betrieb_id);
			}
			
			return $betrieb_id;
		}
		
		return false;
	}
	
	
	
	public function addBetreibskette($data)
	{
		$neu = urldecode($data['neu']);
		$id = $this->insert('
				INSERT INTO '.PREFIX.'kette(`name`)VALUES('.$this->strval($neu).')
		');
		
		$name = safe_html($neu);
		
		return json_encode(array('id'=>$id,'name'=>$name));
	}
	
	public function addOrGetStadtId($stadt,$bundesland)
	{
		$bundesland_id = $this->getBundeslandId($bundesland);

		
		if($id = $this->qone('SELECT `id` FROM `'.PREFIX.'stadt` WHERE `name` = '.$this->strval($stadt).' AND `bundesland_id` = '.$this->intval($bundesland_id)))
		{
			return $id;
		}
		else
		{
			return $this->insert('INSERT INTO `'.PREFIX.'stadt`(`name`,`bundesland_id`)VALUES('.$this->strval($stadt).','.$this->intval($bundesland_id).')');
		}
	}
	
	public function getBundeslandId($name)
	{
		if($id = $this->qOne('SELECT `id` FROM `'.PREFIX.'bundesland` WHERE `name` = '.$this->strval($name)))
		{
			return $id;
		}
		else
		{
			return $this->insert('INSERT INTO `'.PREFIX.'bundesland`(`name`)VALUES('.$this->strval($name).')');
		}
	}
			
	public function getBezirkId($name)
	{
		if($id = $this->qOne('SELECT `id` FROM `'.PREFIX.'bezirk` WHERE `name` = '.$this->strval($name)))
		{
			return $id;
		}
		else
		{
			return $this->insert('INSERT INTO `'.PREFIX.'bezirk`(`name`)VALUES('.$this->strval($name).')');
		}
	}
	
	public function importCities()
	{
	
		$fp = fopen('data/DE_NEU.txt','r');
		$i = 0;
		while (($data = fgetcsv($fp, 1000)) !== FALSE)
		{
			if(strlen($data[1]) == 5)
			{
				if($stadt_id = $this->addOrGetStadtId($data[2],$data[3]))
				{
					if($bezirk_id = $this->getBezirkId($data[7]))
					{
						echo('('.$this->strval($data[1]).','.$this->intval($stadt_id).','.$this->intval($bezirk_id).'),'."\n");
					}
				}
			}
		}
	
	
		fclose($fp);
		die();
	}
	
	public function importCitiesOld()
	{
		$fp = fopen('data/DE.txt','r');
		
		$fpo = fopen('data/DE_NEU.txt','w');
		
		$i = 0;
		$cur_plz = '0';
		$plz = array();
		while (($data = fgetcsv($fp, 1000, "\t")) !== FALSE)
		{
			$plz[$data[1]] = $data;
			
			if(strlen($data[1]) == 5)
			{
				//$this->insert('INSERT INTO `fs_plz`(`name`,`stadt_id`,`bezirk_id`) VALUES()');
			}
			
		}
		
		foreach($plz as $p)
		{
		
			
			fputcsv($fpo,$p);
			
		}
		
		fclose($fpo);
		fclose($fp);
	}
	
	public function getKetten()
	{
		return $this->q('SELECT `id`,`name` FROM '.PREFIX.'kette');
	}
	
	public function getBetriebKategorien()
	{
		return $this->q('SELECT `id`,`name` FROM '.PREFIX.'betrieb_kategorie');
	}
	
	public function getAllFoodsaver()
	{
		return $this->q('
			SELECT 		`'.PREFIX.'foodsaver`.id,
						CONCAT(`'.PREFIX.'foodsaver`.`name`, " ", `'.PREFIX.'foodsaver`.`nachname`) AS `name`,
						`'.PREFIX.'foodsaver`.`anschrift`,
						`'.PREFIX.'foodsaver`.`email`,
						`'.PREFIX.'foodsaver`.`telefon`,
						`'.PREFIX.'foodsaver`.`handy`,
						plz
		
			FROM 		`'.PREFIX.'foodsaver`
		');
	}
	
	
	
	public function getBetriebe($bezirk_id = false)
	{
		if(!$bezirk_id)
		{
			$bezirk_id = $this->getCurrentBezirkId();
		}
		return $this->q('
				SELECT 	'.PREFIX.'betrieb.id, 
						'.PREFIX.'betrieb.plz,  
						'.PREFIX.'betrieb.kette_id, 
						'.PREFIX.'betrieb.betrieb_kategorie_id, 
						'.PREFIX.'betrieb.name, 
						'.PREFIX.'betrieb.str, 
						'.PREFIX.'betrieb.hsnr,
						'.PREFIX.'betrieb.`betrieb_status_id`
				
				FROM 	'.PREFIX.'betrieb
				
				WHERE 	'.PREFIX.'betrieb.bezirk_id = '.$this->intval($bezirk_id).'
				
				
				');// -- AND 	'.PREFIX.'betrieb.bezirk_id = '.$this->intval(1).'
	}
	
	public function may()
	{
		if(isset($_SESSION) && isset($_SESSION['client']) && (int)$_SESSION['client']['id'] > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

  public function begin_transaction() {
    $this->mysqli->query('BEGIN');
  }

  public function commit() {
    $this->mysqli->commit();
  }

  public function rollback() {
    $this->mysqli->rollback();
  }
	
	public function sql($query)
	{
		$res = $this->mysqli->query($query);
		if ($res == false) {
			error_log('SQL QUERY ERROR URL '.$_SERVER['REQUEST_URI'].' IN '.$query.' : '.$this->mysqli->error);
		}
		return $res;
	}
	
	public function qOne($sql)
	{
		if($res = $this->sql($sql))
		{
			if($row = $res->fetch_array())
			{
				if(isset($row[0]))
				{
					return qs($row[0]);
				}
			}
		}
		return false;
	}
	
	public function qCol($sql)
	{
		$out = array();
		if($res = $this->sql($sql))
		{
			while($row = $res->fetch_array())
			{
				$out[] = qs($row[0]);
			}
		}
		
		if(count($out) > 0)
		{
			return $out;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Method to get an asoc array insted the colums are the keys
	 * so aftter all we can check like this if(isset($test[$key])) ...
	 * 
	 * @param string $sql
	 * @return multitype:array |boolean
	 */
	public function qColKey($sql)
	{
		$out = array();
		if($res = $this->sql($sql))
		{
			while($row = $res->fetch_array())
			{
				$val = (int)($row[0]);
				$out[$val] = $val;
			}
		}
	
		if(count($out) > 0)
		{
			return $out;
		}
		else
		{
			return false;
		}
	}
	
	public function qRow($sql)
	{
		
		try {
			$res = $this->sql($sql);
			
			if(is_object($res) && ($row = $res->fetch_assoc()))
			{
				foreach ($row as $i => $r)
				{
					$row[$i] = qs($r);
				}
				return $row;
			}
		}
		catch(Exception $e)
		{
			debug('Error: '.$sql.' => '.$e->getMessage());
		}
		return false;
	}
	
	public function del($sql)
	{
		if($res = $this->sql($sql))
		{
			return $this->mysqli->affected_rows;
		}
		
		return false;
	}
	
	public function insert($sql)
	{
		if($res = $this->sql($sql))
		{
			return $this->mysqli->insert_id;
		}
		else
		{
			return false;
		}
	}
	
	public function boolval($val)
	{
		if($val == 0)
		{
			return 'FALSE';
		}
		else
		{
			return 'TRUE';
		}
	}
	
	public function intval($val)
	{
		return (int)$val;
	}
	
	public function update($sql)
	{
		if($this->sql($sql))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function dateval($val)
	{
		return '"'.$this->safe($val).'"';
	}
	
	public function timeval($val)
	{
		return '"'.$this->safe($val).'"';
	}
	
	public function floatval($val)
	{
		return floatval($val);
	}
	
	public function strval($val,$html = false)
	{
		if(is_string($html) || $html === false)
		{
			if(is_string($html))
			{
				$val = strip_tags($val,$html);
			}
			else
			{
				$val = strip_tags($val);
			}
		}
		return '"'.$this->safe($val).'"';
	}
	
	public function q($sql)
	{
		$out = array();
		if($res = $this->sql($sql))
		{
			while($row = $res->fetch_assoc())
			{
				foreach ($row as $i => $r)
				{
					$row[$i] = qs($r);
				}
				$out[] = $row;
			}
		}
		
		if(count($out) > 0)
		{
			return $out;
		}
		else
		{
			return false;
		}
	}
	
	public function resetPassword($fs_id,$email)
	{
		/*
		$password = nettesPasswort();
		
		$crypt = $this->encryptMd5($email, $password);
		
		$this->update('UPDATE '.PREFIX.'foodsaver SET `passwd` = "'.$crypt.'" WHERE `id` = '.$this->intval($fs_id));
		
		return $password;
		*/
	}
	
	public function encryptMd5($email,$pass)
	{
		$email = strtolower($email);
		
		return md5($email.'-lz%&lk4-'.$pass);
	}
	
	public function __destruct()
	{
		@$this->mysqli->close();
	}
	
	public function safe($str)
	{
		return $this->mysqli->escape_string($str);
	}
	
	public function getFoodsaverId()
	{
		return (int)$_SESSION['client']['id'];
	}
	
	public function relogin()
	{
		$this->initSessionData($_SESSION['client']['id']);
		
		return true;
	}
	
	public function logout()
	{
		//$this->update('UPDATE '.PREFIX.'foodsaver SET `gcm` = "" WHERE id = '.(int)fsId());
		$this->del('DELETE FROM '.PREFIX.'activity WHERE `foodsaver_id` = '.(int)fsId());
		
		Mem::userDel(fsId(), 'active');
		Mem::userDel(fsId(), 'lastMailMessage');
		
	}
	
	public function updateMumble($pass)
	{
		$check = false;
		if(isset($_SESSION['client']['bezirke']) && is_array($_SESSION['client']['bezirke']))
		{
			foreach ($_SESSION['client']['bezirke'] as $b)
			{
				if($b['type'] == 7)
				{
					$check = true;
					break;
				}
			}
		}
		
		if($check)
		{
			$data = $this->getValues(array('name','nachname'),'foodsaver',fsId());
			
			$name = trim($data['name']);
			$name = explode(' ', $name);
			$name = $name[0];
			$name = preg_replace('/[^a-zA-ZäöüÄÖÜß]/', '', $name);
			
			$lastname = trim($data['nachname']);
			$lastname = str_replace(' ', '_', $lastname);
			$lastname = preg_replace('/[^a-zA-ZäöüÄÖÜß_]/', '', $lastname);
			
			$tmpname = $name;
			
			$i=0;			
			while($this->qOne('SELECT COUNT(foodsaver_id) FROM '.PREFIX.'mumbleuser WHERE name = '.$this->strval($tmpname).' AND foodsaver_id != '.(int)fsId()))
			{
				$i++;
				if($i > strlen($lastname))
				{
					$tmpname = $name.'_'.$i;
				}
				else
				{
					$tmpname = $name.'_'.substr($lastname, 0,$i);
				}
				
			}
			
			$this->insert('
					REPLACE INTO `'.PREFIX.'mumbleuser`(`foodsaver_id`, `name`, `sha`) 
					VALUES 
					('.(int)fsId().','.$this->strval($tmpname).','.$this->strval(sha1($pass)).')
			');
		}
	}
	
	public function login($email,$pass)
	{
		if($client = $this->checkClient($email, $pass))
		{
			$this->initSessionData($client['id']);
			
			$this->updateMumble($pass);
			$this->update('
				UPDATE '.PREFIX.'foodsaver
				SET 	last_login = NOW()
				WHERE 	id = '.(int)fsId().'		
			');
			$this->insert('
			INSERT INTO 	`'.PREFIX.'login`
			(
			`foodsaver_id`,
			`ip`,
			`agent`,
			`time`
			)
			VALUES
			(
			'.$this->intval(fsId()).',
			'.$this->strval($_SERVER['REMOTE_ADDR']).',
			'.$this->strval($_SERVER['HTTP_USER_AGENT']).',
			'.$this->dateval(date('Y-m-d H:i:s')).'
			)');
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function gerettet_wrapper($id)
	{
		$ger = array(
				1 => 2,
				2 => 4,
				3 => 7.5,
				4 => 15,
				5 => 25,
				6 => 45,
				7 => 64
		);
	
		if(!isset($ger[$id]))
		{
			return 1.5;
		}
	
		return $ger[$id];
	}
	
	public function checkClient($email,$pass = false)
	{
		$email = $this->safe($email);
		$pass = $this->safe($pass);
		
		//die('<pre>'.$email."\n".$pass);
		$user = false;
		$sql = '
				SELECT 	`id`,
						`bezirk_id`,
						`admin`,
						`orgateam`,
						`photo`
			
				FROM 	`'.PREFIX.'foodsaver`
				WHERE 	`email` = "'.$email.'"
				AND 	`passwd` 	= "'.$this->encryptMd5($email, $pass).'"
		';
		
		if($user = $this->qRow($sql))
		{
			return $user;
		}
		else
		{
			return false;
		}
		
		
	}
	
	/**
	 * Method to check users online status by checking timestamp from memcahce
	 * 
	 * @param integer $fs_id
	 * @return boolean
	 */
	public function isActive($fs_id)
	{
		if($time = Mem::user($fs_id, 'active'))
		{
			if((time()-$time) > 600)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		
		/*
		if($time = $this->qOne('SELECT UNIX_TIMESTAMP(`zeit`) FROM `'.PREFIX.'activity` WHERE `foodsaver_id` = '.$this->intval($fs_id)))
		{
			if((time()-$time) > 600)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		*/
		return false;
	}
	
	public function updateActivity()
	{		
		Mem::userSet(fsId(), 'active', time());
		Mem::userSet(fsId(), 'sid', session_id());
		
		$this->update('UPDATE `'.PREFIX.'activity` SET `zeit` = NOW() WHERE `foodsaver_id` = '.$this->intval(fsId()));
	}
	
	public function dbLoginAs($fs_id)
	{
		return $this->initSessionData($fs_id);
	}
	
	public function initSessionData($fs_id)
	{
		$this->insert('INSERT IGNORE INTO '.PREFIX.'activity(`foodsaver_id`,`zeit`)VALUE('.$this->intval($fs_id).',NOW()) ');
		$this->updateActivity();
		if($fs = $this->qRow('
				SELECT 		`id`,
							`admin`,
							`orgateam`,
							`bezirk_id`,
							`photo`,
							`rolle`,
							`type`,
							`verified`,
							`name`,
							`nachname`,
							`lat`,
							`lon`,
							`email`,
							`token`
				
				FROM 		`'.PREFIX.'foodsaver`

				WHERE 		`id` = '.$this->intval($fs_id).'
		'))
		{
			S::set('g_location', array(
				'lat' => $fs['lat'],
				'lon' => $fs['lon']		
			));
			S::set('badge-info', (int)$this->qOne('SELECT COUNT(bell_id) FROM '.PREFIX.'foodsaver_has_bell WHERE foodsaver_id = '.(int)$fs_id.' AND seen = 0'));
			S::set('badge-msg', (int)$this->qOne('SELECT COUNT(conversation_id) FROM '.PREFIX.'foodsaver_has_conversation WHERE foodsaver_id = '.(int)$fs_id.' AND unread = 1'));
			S::set('badge-basket', 0);
			$this->insert('
				INSERT IGNORE INTO `'.PREFIX.'foodsaver_has_bezirk`(`foodsaver_id`, `bezirk_id`, `active`, `added`) VALUES 
				('.(int)$fs['id'].','.(int)$fs['bezirk_id'].',1,NOW())
			');
			
			if($master = $this->getVal('master','bezirk',$fs['bezirk_id']))
			{
				$this->insert('
					INSERT IGNORE INTO `'.PREFIX.'foodsaver_has_bezirk`(`foodsaver_id`, `bezirk_id`, `active`, `added`) VALUES
					('.(int)$fs['id'].','.(int)$master.',1,NOW())
				');
			}
			
			if($fs['photo'] != '' && file_exists('images/mini_q_'.$fs['photo']))
			{
				$image1 = new fImage('images/mini_q_'.$fs['photo']);
				if($image1->getWidth() > 36)
				{
					$image1->cropToRatio(1, 1);
					$image1->resize(35, 35);
					$image1->saveChanges();
				}
			}
			
			/*
			 * New Session Management
			 */ 
			S::login($fs);
			
			/*
			 * store all options in the session
			*/
				
			if(!empty($fs['option']))
			{
				$options = unserialize($fs['option']);
				foreach ($options as $key => $val)
				{
					S::setOption($key, $val);
				}
			}
			
			$_SESSION['login'] = true;
			$_SESSION['client'] = array
			(
				'id' => $fs['id'],
				'bezirk_id' => $fs['bezirk_id'],
				'group' => array('member' => true),
				'photo' => $fs['photo'],
				'rolle' => (int)$fs['rolle'],
				'verified' => (int)$fs['verified']
			);
			if($fs['admin'] == 1)
			{
				$_SESSION['client']['group']['admin'] = true;
			}
			if($fs['orgateam'] == 1)
			{
				$_SESSION['client']['group']['orgateam'] = true;
			}
			
			if($r = $this->q('
						SELECT 	`'.PREFIX.'botschafter`.`bezirk_id`,
								`'.PREFIX.'bezirk`.`has_children`,
								`'.PREFIX.'bezirk`.`parent_id`,
								`'.PREFIX.'bezirk`.name,
								`'.PREFIX.'bezirk`.id,
								`'.PREFIX.'bezirk`.type
			
						FROM 	`'.PREFIX.'botschafter`,
								`'.PREFIX.'bezirk`
			
						WHERE 	`'.PREFIX.'bezirk`.`id` = `'.PREFIX.'botschafter`.`bezirk_id`
			
						AND 	`'.PREFIX.'botschafter`.`foodsaver_id` = '.$this->intval($fs['id']).'
				'))
			{
				$_SESSION['client']['botschafter'] = $r;
				$_SESSION['client']['group']['botschafter'] = true;
				
				foreach ($r as $rr)
				{
					if(!$this->q('SELECT foodsaver_id FROM `'.PREFIX.'foodsaver_has_bezirk` WHERE foodsaver_id = '.$this->intval($fs['id']).' AND bezirk_id = '.(int)$rr['id'].' AND active = 1'))
					{
						$this->insert('
						REPLACE INTO `'.PREFIX.'foodsaver_has_bezirk`
						(
							`bezirk_id`,
							`foodsaver_id`,
							`active`,
							`added`
						)
						VALUES
						(
							'.(int)$rr['id'].',
							'.(int)$fs['id'].',
							1,
							NOW()
						)
					');
					}
					
				}
			}
			
			if($r = $this->q('
						SELECT 	b.`id`,
								b.name,
								b.type,
								b.`master`
				
						FROM 	`'.PREFIX.'foodsaver_has_bezirk` hb,
								`'.PREFIX.'bezirk` b
				
						WHERE 	hb.bezirk_id = b.id 	
						AND 	`foodsaver_id` = '.$this->intval($fs['id']).'
						AND 	hb.active = 1
					
						ORDER BY b.name
				'))
			{
				$_SESSION['client']['bezirke'] = array();
				$mastercheck = array();
				foreach ($r as $rr)
				{
					$_SESSION['client']['bezirke'][$rr['id']] = array(
						'id' => $rr['id'],
						'name' => $rr['name'],
						'type' => $rr['type']
					);
					$mastercheck[$rr['master']] = $rr['master'];
				}
				foreach ($mastercheck as $m)
				{
					/*
					if(!isset($_SESSION['client']['bezirke'][$m]) && (int)$m > 0)
					{
						$this->insert('
							INSERT INTO `'.PREFIX.'foodsaver_has_bezirk`
							(
								`foodsaver_id`,
								`bezirk_id`,
								`active`,
								`added`
							)
							VALUES
							(
								'.(int)$fs['id'].',
								'.(int)$m.',
								1,
								NOW()
							)
						');
						$_SESSION['client']['bezirke'][$m] = $this->getValues(array('id','name','type'), 'bezirk', $m);
					}*/
				}
			}
			$_SESSION['client']['betriebe'] = false;
			if($r = $this->q('
						SELECT 	b.`id`,
								b.name
			
						FROM 	`'.PREFIX.'betrieb_team` bt,
								`'.PREFIX.'betrieb` b
			
						WHERE 	bt.betrieb_id = b.id
						AND 	bt.`foodsaver_id` = '.$this->intval($fs['id']).'
						AND 	bt.active = 1
				'))
			{
				$_SESSION['client']['betriebe'] = array();
				foreach ($r as $rr)
				{
					$_SESSION['client']['betriebe'][$rr['id']] = $rr;
				}
			}
			
			
			
			if($r = $this->q
			('
						SELECT 	`betrieb_id`
				
						FROM 	`'.PREFIX.'betrieb_team`
				
						WHERE 	`foodsaver_id` = '.$this->intval($fs['id']).'
						AND 	`verantwortlich` = 1
			'))
			{
				$_SESSION['client']['verantwortlich'] = $r;
				$_SESSION['client']['group']['verantwortlich'] = true;
			}			
		}
		else
		{
			goPage('logout');
		}
	}
	
	public function getTables()
	{
		$out = $this->q('SHOW TABLES');
		
		$tables = array();
		foreach ($out as $t)
		{
			$tables[] = end($t);
		}
		
		$out = array();
		foreach ($tables as $key => $t)
		{
			$out[$t] = $this->q('SHOW FULL COLUMNS FROM `'.$t.'`');
		}
		return $out;
	}
	
	public function addClient($email,$pass)
	{
		$email = strtolower($email);
		//$email = trim($email);
	
		$md5 = $this->encryptMd5($email, $pass);
	
		$sql = '
		INSERT INTO `'.PREFIX.'foodsaver`	(`email`, `passwd`)
		VALUES					('.$this->strval($email).','.$this->strval($md5).')
		';
	
		if($id = $this->insert($sql))
		{
			return $id;
		}
		else
		{
			return false;
		}
	}
	
	public function getValues($fields,$table,$id)
	{
		$fields = implode('`,`', $fields);
		
		return $this->qRow('
			SELECT 	`'.$fields.'`
			FROM 	`'.PREFIX.$table.'`
			WHERE 	`id` = '.$this->intval($id).'		
		');
	}
	
	public function getVal($field,$table,$id)
	{
		if(!isset($this->values[$field.'-'.$table.'-'.$id]))
		{
			$this->values[$field.'-'.$table.'-'.$id] = $this->qOne('
			SELECT 	`'.$field.'`
			FROM 	`'.PREFIX.$table.'`
			WHERE 	`id` = '.$this->intval($id).'		
		');
		}
		
		return $this->values[$field.'-'.$table.'-'.$id];
	}
	
	public function updateFields($fields,$table,$id)
	{
		global $db;
		$sql = array();
		foreach ($fields as $k => $f)
		{
			if(preg_replace('/[^0-9]/', '', $f) == $f)
			{
				$sql[] = '`'.$k.'`='.$db->intval($f);
			}
			else
			{
				$sql[] = '`'.$k.'`='.$db->strval($f);
			}
		}
		return  $this->update('UPDATE `'.PREFIX.$table.'` SET '.implode(',',$sql).' WHERE `id` = '.(int)$id);
	}
	
	public function getTable($fields,$table,$where = '')
	{		
		return $this->q('
			SELECT 	`'.implode('`,`', $fields).'`
			FROM 	`'.PREFIX.$table.'`
			'.$where.'
		');
	}
	
	/**
	 * set option is an key value store each var is avalable in the user session
	 * 
	 * @param string $key
	 * @param var $val
	 */
	public function setOption($key,$val)
	{
		$options = array();
		if($opt = $this->getVal('option', 'foodsaver', fsId()))
		{
			$options = unserialize($opt);
		}
		
		$options[$key] = $val;
		$this->update('UPDATE '.PREFIX.'foodsaver SET option = '.$this->strval(serialize($options)).' WHERE id = '.(int)fsId());
 	}
}
