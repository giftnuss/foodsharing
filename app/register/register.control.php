<?php
class RegisterControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new RegisterModel();
		$this->view = new RegisterView();
		
		parent::__construct();
		
	}

	private $fields_required = array('name' => true, 'geb_datum' => true, 'address' => true, 'ort' => true, 'email' => true, 'phone' => false, 'take_part' => true, 'sleep_at' => false, 'sleep_slots' => true, 'languages' => false, 'nutrition' => false, 'special_nutriton' => false, 'other_languages' => false, 'translation_necessary' => false, 'already_foodsaver' => false, 'childcare' => false, 'comments' => false);

	private $salt = 'Z3SzsG6nEgXX43CJyRf55o7Y_6v';
	
	public function index()
	{
		global $g_data;
		$lang = 'de';
		if(isset($_REQUEST['lang']) && $_REQUEST['lang'] == 'en') {
			$lang = 'en';
		}
		if(getPost('form_submit') == 'signup_meeting')
		{
			$this->handleSignup();
		} else
		{
			// Prefill signup page with data from login
			if($lang == 'de') {
				$infotext = $this->model->getContent(40);
			} else {
				$infotext = $this->model->getContent(41);
			}
			if(S::may()) {
				$fs = $this->model->getOne_foodsaver(fsId());
				$g_data['rolle'] = $fs['rolle'];
				$g_data['name'] = $fs['name'].' '.$fs['nachname'];
				$g_data['geb_datum'] = $fs['geb_datum'];
				$g_data['address'] = $fs['anschrift'].' '.$fs['plz'];
				$g_data['ort'] = $fs['stadt'];
				$g_data['email'] = $fs['email'];
				$g_data['phone'] = $fs['handy'];
			}
			$this->view->signup($infotext);
		}
	}

	private function myGetPost($k)
	{
		if(isset($_POST[$k]))
		{
			return $_POST[$k];
		}
		return false;
	}

	private function validateInputData($fields)
	{
		global $g_data;
		$vals = array();
		foreach($fields as $k=>$v)
		{
			$val = $this->myGetPost($k);
			if($val !== false)
			{
				$vals[$k] = $val;
			}
			$g_data[$k] = $val;
		}
		return $vals;
	}

	private function handleSignup()
	{
		$fields = $this->validateInputData($this->fields_required);
		$missing_fields = array_diff_key($this->fields_required, $fields);
		$missing_required_fields = array_filter($missing_fields, function($val) { return $val; });
		$fields['ip'] = $_SERVER['REMOTE_ADDR'];
		if(fsid() > 0)
		{
			$fields['foodsaver_id'] = fsid();
		}
		if(!empty($missing_required_fields))
		{
			$this->view->signupError('err_not_all_req_fields', implode(', ', array_keys($missing_required_fields)));
		} elseif(!validEmail($fields['email']))
		{
			$this->view->signupError('err_email');
		} elseif($this->model->isIpBlock($_SERVER['REMOTE_ADDR']))
		{
			$this->view->signupError('err_wait_moment');
		} elseif($this->model->alreadyRegistered(getPost('email')))
		{
			$this->view->signupError('err_already_registered');
		} elseif(!$this->model->register($fields))
		{
			$this->view->signupError('err_unknown');
		} else
		{
			$validationCode = sha1($this->salt.$fields['email']);
			tplMail(23, $fields['email'], array('anrede' => 'Liebe/r', 'name' => $fields['name'],
				'link' => 'https://'.DEFAULT_HOST.'/?page=register&validate='.$fields['email'].'&code='.$validationCode));
			$this->view->signupOkay();
		}
	}
}
