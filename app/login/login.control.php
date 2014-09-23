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
			if(isset($_POST['email_adress']))
			{
				$this->handleLogin();
			}
			
			addJs('
				if(isMob())
				{
					$("#ismob").val("1");
				}
				$(window).resize(function(){
					if(isMob())
					{
						$("#ismob").val("1");
					}
					else
					{
						$("#ismob").val("0");
					}
				});
						
				$("#login-form").submit(function(ev){
					$("#ismob").val("1");
				}
				$(window).resize(function(){
					if(isMob())
					{
						$("#ismob").val("1");
					}
					else
					{
						$("#ismob").val("0");
					}
				});		
			');
			
			addContent($this->view->login());
		}
		else
		{
			go('/?page=dashboard');
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
}