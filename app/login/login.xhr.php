<?php 
class LoginXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new LoginModel();
		$this->view = new LoginView();

		parent::__construct();
		
		
		
	}
	
	/**
	 * Method to generate search Index for instant seach
	 */
	private function genSearchIndex()
	{
		/*
		 * The big array we want to fill ;)
		 */
		$index = array();
		
		/*
		 * Buddies Load persons in the index array that connected with the user
		 */
		
		$model = loadModel('buddy');
		if($buddies = $model->listBuddies())
		{
			$result = array();
			foreach ($buddies as $b)
			{
				$result[] = array(
					'name' => $b['name'].' '.$b['nachname'],
					'teaser' => '',
					'img' => img($b['photo']),
					'click' => 'profile(\''.$b['id'].'\');',
					'search' => array(
						$b['name'],$b['nachname']
					)
				);
			}
			$index[] = array(
				'title' => 'Menschen die Du kennst',
				'result' => $result
			);
		}
		
		/*
		 * Groups load Groups connected to the user in the array
		*/
		$model = loadModel('groups');
		if($groups = $model->listMyGroups())
		{
			$result = array();
			foreach ($groups as $b)
			{
				$img = false;
				if(!empty($b['photo']))
				{
					$img = 'images/photo/thumb_' . $b['photo'];
				}
				$result[] = array(
						'name' => $b['name'],
						'teaser' => tt($b['teaser'],80),
						'img' => $img,
						'href' => '?page=bezirk&id='.$b['id'].'&sub=bezirk',
						'search' => array(
							$b['name']
						)
				);
			}
			$index[] = array(
					'title' => 'Gruppen',
					'result' => $result
			);
		}
		
		/*
		 * Betriebe load food stores connected to the user in the array
		 */
		$model = loadModel('betrieb');
		if($betriebe = $model->listMyBetriebe())
		{
			$result = array();
			foreach ($betriebe as $b)
			{
				$result[] = array(
						'name' => $b['name'],
						'teaser' => $b['str'].' '.$b['hsnr'].', '.$b['plz'].' '.$b['stadt'],
						'href' => '?page=fsbetrieb&id='.$b['id'],
						'search' => array(
							$b['name'],$b['str']
						)
				);
			}
			$index[] = array(
					'title' => 'Betriebe',
					'result' => $result
			);
		}
		
		/*
		 * Bezirke load Bezirke connected to the user in the array
		*/
		$model = loadModel('bezirk');
		if($bezirke = $model->listMyBezirke())
		{
			$result = array();
			foreach ($bezirke as $b)
			{
				$result[] = array(
						'name' => $b['name'],
						'teaser' => '',
						'img' => false,
						'href' => '?page=bezirk&id='.$b['id'].'&sub=bezirk',
						'search' => array(
								$b['name']
						)
				);
			}
			$index[] = array(
					'title' => 'Bezirke',
					'result' => $result
			);
		}
		
		/*
		 * Get or set an individual token as filename for the public json file
		*/
		if($token = S::user('token'))
		{
			file_put_contents('cache/searchindex/' . $token . '.json',json_encode($index));
			return $token;
		}
		
		
		return false;
	}
	
	public function login()
	{
		if(!S::may())
		{
			$dia = new XhrDialog();
			
			$dia->setTitle(s('login'));
			
			$dia->addContent($this->view->loginForm());
			
			$dia->addButton('Registrieren','alert(0);');
			$dia->addButton('Einloggen',"ajreq('loginsubmit',{app:'login',u:$('#email_adress').val(),p:$('#password').val()});");
			
			return $dia->xhrout();
		}
	}
	
	public function loginsubmit()
	{
		if($this->model->login($_GET['u'],$_GET['p']))
		{
			$token_js = '';
			if($token = $this->genSearchIndex())
			{
				$token_js = 'user.token = "'.$token.'";';
			}
			
			$menu = getMenu();
			$msgbar = v_msgBar();
			return array(
				'status' => 1,
				'script' => '
					'.$token_js.'
					pulseSuccess("'.s('login_success').'");
					closeAllDialogs();
					$("#layout_logo").after(\''.jsSafe($msgbar).'\');
					$("#mainMenu").replaceWith(\''.jsSafe($menu['default']).'\');
					$("#mainMenu").jMenu({
						ulWidth:200,
						absoluteTop:37,
						TimeBeforeClosing : 0,
						TimeBeforeOpening : 0,
				        effects : {
				          effectSpeedOpen : 0,
				          effectSpeedClose : 0
				      	},
					});
					infoMenu();
					$("#layout_logo a").attr("href","/?page=dashboard");'
			);
		}
		else 
		{
			return array(
					'status' => 1,
					'script' => 'pulseError("'.s('login_failed').'",{sticky:true});'
			);
		}
	}
}