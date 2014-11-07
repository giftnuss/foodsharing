<?php
class LoginControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new LoginModel();
		$this->view = new LoginView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		if(!S::may())
		{
			if(!isset($_GET['sub']))
			{
				if(isset($_POST['email_adress']))
				{
					$this->handleLogin();
				}
				addContent($this->view->login());
			}
		}
		else
		{
			go('/?page=dashboard');
		}
	}
	
	public function activate()
	{
		if($this->model->activate($_GET['e'],$_GET['t']))
		{
			info(s('activation_success'));
			goPage('login');
		}
		else 
		{
			error(s('activation_failed'));
			goPage('login');
		}
	}
	
	private function handleLogin()
	{
		if($this->model->login($_POST['email_adress'],$_POST['password']))
		{
			$this->model->add_login(array(
				'foodsaver_id' => fsId(),
				'ip' => $_SERVER['REMOTE_ADDR'],
				'time' => date('Y-m-d H:i:s'),
				'agent' => $_SERVER['HTTP_USER_AGENT']
			));
			info(s('login_success'));
			
			if(isset($_POST['ismob']))
			{
				$_SESSION['mob'] = (int)$_POST['ismob'];
			}
			
			require_once 'lib/Mobile_Detect.php';
			$mobdet = new Mobile_Detect();
			if($mobdet->isMobile())
			{
				$_SESSION['mob'] = 1;
			}
			
			if(strpos($_SERVER['HTTP_REFERER'],URL_INTERN) !== false || isset($_GET['logout']))
			{
				if(isset($_GET['ref']))
				{
					go(urldecode($_GET['ref']));
				}
				go(str_replace('?page=login&logout','?page=dashboard',$_SERVER['HTTP_REFERER']));
			}
			else
			{
				go('?page=dashboard');
			}
			
			
		}
		else
		{
			error('Falsche Zugangsdaten');
		}
	}
	
	public function passwordReset()
	{
		$k = false;
		
		if(isset($_GET['k']))
		{
			$k = strip_tags($_GET['k']);
		}
		
		addTitle('Password zurücksetzen');
		addBread('Passwort zurücksetzen');
		
		if(isset($_POST['email']) || isset($_GET['m']))
		{
			$mail = '';
			if(isset($_GET['m']))
			{
				$mail = $_GET['m'];
			}
			else
			{
				$mail = $_POST['email'];
			}
			if(!validEmail($mail))
			{
				error('Sorry','Hast Du Dich vielleicht bei Deiner E-Mail Adresse vertippt?');
			}
			else
			{
				if($this->model->addPassRequest($mail))
				{
					info('Alles klar!, Dir wurde ein Link zum Passwort ändern per E-Mail zugeschickt<br />');
						
				}
				else
				{
					error('Sorry, Diese E-Mail Adresse ist uns nicht bekannt');
				}
			}
		}
		
		//$this->template->addRight($this->getOrgaTeam());
		
		if($k !== false && $this->model->checkResetKey($k))
		{
			if($this->model->checkResetKey($k))
			{
				if(isset($_POST['pass1']) && isset($_POST['pass2']))
				{
					if($_POST['pass1'] == $_POST['pass2'])
					{
						$check = true;
						if($this->model->newPassword($_POST))
						{
							success('Prima, Dein Passwort wurde erfolgreich geändert, jetzt kannst Du Dich einloggen');
						}
						elseif(strlen($_POST['pass1']) < 5)
						{
							$check = false;
							error('Sorry, Dein gewähltes Passwort ist zu kurz!');
						}
						elseif(strlen($_POST['pass1']) >= 30)
						{
							$check = false;
							error('Sorry, Dein gewähltes Passwort ist zu lang!');
						}
						elseif(!$this->model->checkResetKey($_POST['k']))
						{
							$check = false;
							error('Sorry, Du hast zu lang gewartet, bitte beantrage ein nocheinmal ein neues Passwort!');
						}
						else
						{
							$check = false;
							error('Sorry, Es gibt ein Problem mir Deinen Daten, es wurde ein Administrator informiert');
							/*
							tplMail(11, 'kontakt@prographix.de',array(
								'data' => '<pre>'.print_r($_POST,true).'</pre>'
							));
							*/
						}
						
						if($check)
						{
							go('?page=login');
						}
					}
					else
					{
						error('Sorry, Die Passwörter stimmen nicht überein');
					}
				}
				addJs('$("#pass1").val("");');
				addContent($this->view->newPasswordForm($k));
			}
			else
			{
		
				$this->template->addLeft($this->view->error('Sorry!', 'Du hast ein bisschen zu lange gewartet, bitte beantrage ein neues Passwort'));
				$this->template->addLeft($this->view->passwordRequest());
			}
		}
		else
		{
			addContent($this->view->passwordRequest());
		}
	}
}