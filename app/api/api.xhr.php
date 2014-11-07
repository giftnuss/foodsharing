<?php 
class ApiXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new ApiModel();
		$this->view = new ApiView();

		if(isset($_POST['token']))
		{
			//session_id($_POST['token']);
		}
		
		parent::__construct();
		
		if(!S::may() && $_GET['m'] != 'login')
		{
			return $this->appout(array(
					'status' => 2
			));
		}
	}
	
	public function udata()
	{
		if($user = $this->model->getValues(array('id','name','photo'), 'foodsaver', $_GET['i']))
		{
			return $this->appout(array(
				'status' => 1,
				'user' => $user
			));
		}
		
		return $this->appout(array(
				'status' => 0
		));
	}
	
	public function sendmsg()
	{
		$message = strip_tags($_GET['ms']);
		$message = trim($message);
		
		$model =loadModel('msg');
		
		if((int)$_GET['id'] > 0 && $message != '')
		{
			//file_put_contents('/tmp/lmr/chat.log', fsid().':'.(int)$_GET['id'].':'.$message."\n",FILE_APPEND);
			
			if($conversation_id = $model->user2conv($_GET['id']))
			{
				$id = $model->sendMessage($conversation_id,$message);
				
				$user = $this->model->getValues(array('iosid','gcm'), 'foodsaver', $_GET['id']);
					
				if(!empty($user['gcm']) || !empty($user['iosid']))
				{
					$this->model->addPushQueue(
						fsId(), 
						$_GET['id'], 
						S::user('name').' hat Dir eine Nachricht geschrieben', 
						$message,
						array(
							'gcm' => $user['gcm'],
							'iosid' => $user['iosid']
						),
						array('t' => 0,'i'=>(int)fsId(),'c' => time()),
						$id
					);
				}
				return $this->appout(array(
						'status' => 1,
						'time' => time(),
						'msg' => $message,
						'id' => (int)$_GET['id']
				));
			}
		}
		
		return $this->appout(array(
			'status' => 0
		));
	}
	
	public function chathistory()
	{
		$model = loadModel('msg');
		
		if($history = $model->chatHistory($_GET['id']))
		{
			return $this->appout(array(
				'status' => 1,
				'history' => $history,
				'user' => $this->model->getValues(array('id','name','photo'), 'foodsaver', $_GET['id'])
			));
		}
		
		return $this->appout(array(
				'status' => 0
		));
	}
	
	public function upload()
	{
		$ext = explode('.',$_FILES['file']['name']);
		$ext = end($ext);
		
		$pic = uniqid() . '.' . $ext;
		move_uploaded_file($_FILES['file']['tmp_name'],'tmp/' . $pic);

		echo $pic;
		exit();
	}
	
	public function logout()
	{
		$this->model->logout();
		$_SESSION['login'] = false;
		$_SESSION = array();
		
		S::destroy();
		
		return $this->appout(array(
			'status' => 1
		));
	}
	
	public function login()
	{
		if(isset($_GET['e']))
		{
			if($this->model->login($_GET['e'], $_GET['p']))
			{
				if(isset($_GET['g']) && strlen($_GET['g']) > 3)
				{
					$this->model->setgcm($_GET['g']);
				}
				$fs = $this->model->getValues(array('telefon','handy','geschlecht','name','lat','lon','photo'), 'foodsaver', fsId());

				$this->appout(array(
						'status' => 1,
						'token' => session_id(),
						'gender' => $fs['geschlecht'],
						'phone' => $fs['telefon'],
						'phone_mobile' => $fs['handy'],
						'id' => fsId(),
						'name' => $fs['name'],
						'lat' => $fs['lat'],
						'lon' => $fs['lon'],
						'photo' => $fs['photo']
				));
			}
		}
		
		$this->appout(array(
			'status' => 0
		));
	}
	
	public function initRelogin()
	{
		$this->appout(array(
				'status' => 0
		));
	}
	
	public function basket_submit()
	{
		if(S::may())
		{
			/*
			 * Array
			(
			    [desc] => g
			    [art] => 2,4
			    [types] => 2,4
			    [fetchart] => loc
			    [lat] => 
			    [lon] => 
			)
			 */
			
			$desc = strip_tags($_GET['desc']);
			$tmp = array();
			
			// Bio vegan ...
			if(isset($_GET['art']))
			{
				$art = $_GET['art'];
				foreach ($art as $a)
				{
					if((int)$a > 0)
					{
						$tmp[] = (int)$a;
					}
				}
			}
			$art = $tmp;
			
			$tmp = array();
			
			// Essens-Arten
			if(isset($_GET['types']))
			{
				$types = $_GET['types'];
				foreach ($types as $t)
				{
					if((int)$t > 0)
					{
						$tmp[] = (int)$t;
					}
				}
			}
			$types = $tmp;
			
			$tmp = array();
			
			// contact types
			if(isset($_GET['ctype']))
			{
				$ctypes = $_GET['ctype'];
				
				foreach ($ctypes as $t)
				{
					if(in_array((int)$t, array(1,2)))
					{
						$tmp[(int)$t] = (int)$t;
					}
				}
			}
			$ctypes = $tmp;
			
			if(empty($ctypes))
			{
				$ctypes = array(1);
			}
			
			$model = loadModel('basket');
			
			
			
			if(!empty($desc))
			{
				$weight = floatval($_GET['weight']);
				if($weight <= 0)
				{
					$weight = 3;
				}
				
				$tel = array(
					'tel' => preg_replace('[^0-9\ \+]', '', $_GET['phone']),
					'handy' => preg_replace('[^0-9\ \+]', '', $_GET['phone_mobile'])
				);
				
				$photo = '';
				if(isset($_GET['photo']) && !empty($_GET['photo']))
				{
					if($this->resizePic($_GET['photo']))
					{
						$photo = strip_tags($_GET['photo']);
					}
				}
				
				$fs = $this->model->getValues(array('lat','lon'),'foodsaver',fsId());
					
				$lat = $fs['lat'];
				$lon = $fs['lon'];
				
				if($_GET['fp'] == 'loc')
				{
					$llat = floatval($_GET['lat']);
					$llon = floatval($_GET['lon']);
					
					if(strlen($lat.'') > 2 && strlen($lon.'') > 2)
					{
						$lat = $llat;
						$lon = $llon;
					}
				}
				
				if($id = $model->addBasket(
						$desc,
						$photo, // pic
						$tel, // phone
						implode(':', $ctypes),
						$weight, // weight
						(int)$_GET['fetchart'], // location type
						$lat, // lat
						$lon, // lon
						S::user('bezirk_id')
				))
				{
					if(!empty($art))
					{
						$model->addArt($id,$art);
					}
					if(!empty($types))
					{
						$model->addTypes($id,$types);
					}
					
					return $this->appout(array(
						'status' => 1
					));
					
				}
			}
			
		}
		
		return $this->initRelogin();
	}
	
	public function resizePic($pic)
	{
		if(file_exists('tmp/' . $pic))
		{
			copy('tmp/' . $pic, 'images/basket/' . $pic);
			$this->chmod('images/basket/' . $pic, 777);
			
			$img = new fImage('images/basket/' . $pic);
			
			$img->resize(800, 800);
			$img->saveChanges();
			
			copy('images/basket/' . $pic, 'images/basket/thumb-' . $pic);
			copy('images/basket/' . $pic, 'images/basket/medium-' . $pic);
			
			$this->chmod('images/basket/thumb-' . $pic, 777);
			$this->chmod('images/basket/medium-' . $pic, 777);
			
			$img = new fImage('images/basket/thumb-' . $pic);
			$img->cropToRatio(1, 1);
			$img->resize(200, 200);
			$img->saveChanges();
			
			$img = new fImage('images/basket/medium-' . $pic);
			$img->resize(450, 450);
			$img->saveChanges();
			
			return true;
		}
		return false;
	}
	
	private function chmod($file,$mode)
	{
		exec('chmod 777 /var/www/lmr-v1/freiwillige/' . $file);
	}
	
	public function checklogin()
	{
		if(S::may())
		{
			$this->appout(array(
				'status' => 1
			));
		}
		$this->appout(array(
				'status' => 0
		));
	}
	
	public function orgagruppen()
	{
		if($groups = $this->model->getOrgaGroups())
		{
			$this->out($groups);
		}
	}
	
	public function mumbleusers()
	{
		if($users = $this->model->getMumbleUsers())
		{
			$this->out($users);
		}
	}
	
	public function auth()
	{		
		if($ret = $this->model->checkClient($_GET['user'],$_GET['pass']))
		{
			$values = $this->model->getValues(array('id','orgateam','name','email','photo','geschlecht'), 'foodsaver', $ret['id']);
			
			$values['bot'] = $this->model->isBotschafter($ret['id']);
			
			$values['menu'] = false;
			if(file_exists('menus/'.$ret['id'].'_menu.html'))
			{
				$values['menu'] = file_get_contents('menus/'.$ret['id'].'_menu.html');
			}
			
			$this->out(array(
				'status' => 1,
				'data' => $values
			));	
		}
		else
		{
			$this->out(array(
				'status' => 0
			));
		}
	}
	
	private function out($data)
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		echo json_encode($data);
		exit();
	}
	
	public function loadBasket()
	{
		if(S::may())
		{
			if($basket = $this->model->getBasket($_GET['id']))
			{
				return $this->appout(array(
					'status' => 1,
					'basket' => $basket
				));
			}
		}
		return $this->appout(array(
			'status' => 0		
		));
	}
	
	public function allbaskets()
	{
		if(S::may())
		{
			if($baskets = $this->model->allBaskets())
			{
				$this->appout(array(
					'status' => 1,
					'baskets' => $baskets
				));
			}
		}
		$this->appout(array(
			'status' => 0
		));
	}
	
	public function basketsnear()
	{
		if(S::may() && isset($_GET['lat']) && isset($_GET['lon']))
		{
			$lat = floatval($_GET['lat']);
			$lon = floatval($_GET['lon']);
			
			if($baskets = $this->model->nearBaskets($lat,$lon))
			{
				$this->appout(array(
					'status' => 1,
					'baskets' => $baskets		
				));
			}
		}
		$this->appout(array(
			'status' => 0
		));
	}
	
	public function loadrequests()
	{
		//$model = loadModel('basket');
		//if($baskets = $model->listUpdates())
		if($conv = $this->model->getConversations())
		{
			$out = array();
			foreach ($conv as $b)
			{
				$out[] = array(
					't' => niceDateShort($b['time_ts']),
					'n' => $b['name'],
					'id' => $b['sender_id'],
					'p' => $b['photo'],
					'm' => ''
				);
			}
			
			return $this->appout(array(
				'status' => 1,
				'requests' => $out
			));
		}
		return $this->appout(array(
			'status' => 0
		));
	}
	
	public function setiosid()
	{
		if(S::may() && isset($_GET['i']) && !empty($_GET['i']))
		{
			$this->model->setiosid($_GET['i']);
			return $this->appout(array(
				'status' => 1
			));
		}
		return $this->appout(array(
				'status' => 0
		));
	}
	
	public function setgcm()
	{
		if(S::may() && isset($_GET['i']) && !empty($_GET['i']))
		{
			$this->model->setgcm($_GET['i']);
			return $this->appout(array(
				'status' => 1		
			));
		}
		return $this->appout(array(
			'status' => 0
		));
	}
}