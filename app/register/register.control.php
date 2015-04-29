<?php
class RegisterControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new RegisterModel();
		$this->view = new RegisterView();
		mb_internal_encoding('UTF-8');
		
		parent::__construct();
		
	}

	private $fields_required = array('name' => true, 'geb_datum' => true, 'ort' => true, 'email' => true, 'phone' => false, 'take_part' => true, 'sleep_at' => false, 'sleep_slots' => true, 'languages' => false, 'languages_translate' => false, 'nutrition' => false, 'special_nutrition' => false, 'other_languages' => false, 'other_languages_translate' => false, 'translation_necessary' => false, 'already_foodsaver' => false, 'childcare' => false, 'comments' => false, 'available_thursday' => false);

	private $list_allowed = array(37,41,58,101,133,222,770,953,966,1163,1770,2038,2630,2855,2943,3040,3166,3327,3452,3743,3825,4173,4282,6632,6844,7801,8327,81417,83340);
	private $salt = 'Z3SzsG6nEgXX43CJyRf55o7Y_6v';

	private function calcValidationCode($email)
	{
		return sha1($this->salt.$email);
	}
	
	private function sendRegistrationMail($name, $email)
	{
	    $validationCode = $this->calcValidationCode($email);
	    tplMail(29, $email, array('anrede' => 'Liebe/r', 'name' => $name,
	    'link' => 'https://foodsharing.de/?page=register&validate='.$email.'&code='.$validationCode,
	    'link_en' => 'https://foodsharing.de/?page=register&validate='.$email.'&lang=en&code='.$validationCode,
	    'workshop' => 'https://foodsharing.de/?page=register&validate='.$email.'&workshops&code='.$validationCode,
	    'workshop_en' => 'https://foodsharing.de/?page=register&validate='.$email.'&workshops&lang=en&code='.$validationCode));
	}
	
	public function index()
	{
		global $g_data;
		$lang = 'de';
		if(isset($_REQUEST['lang']) && $_REQUEST['lang'] == 'en') {
			$lang = 'en';
		}
		if($lang == 'de') {
			$infotext = $this->model->getContent(40);
		} else {
			$infotext = $this->model->getContent(41);
		}
		if((S::may('orga') || in_array(fsid(), $this->list_allowed)) && isset($_REQUEST['list']))
		{ // Sascha or Orga: List page
		    if(isset($_REQUEST['form_submit']))
		    {
		        $seen = array();
		        $comments = array();
		        foreach($_POST as $k=>$v)
		        {
		            $pat = "/^on_place(\d+)$/";
		            if(preg_match($pat, $k, $res))
		            {
		                $seen[$res[1]] = True;
		            }
		            $pat = "/^admin_comment(\d+)$/";
		            if(preg_match($pat, $k, $res))
		            {
		                $comments[$res[1]] = $v;
		            }
		        }
		        $this->model->updateSeen($seen);
		        $this->model->updateComments($comments);
		        goPage('register&list');
		    }
		    if(isset($_REQUEST['newsletter']))
		    {
		        $registrations = $this->model->getRegistrations(array('email' => True, 'name' => True));
		        foreach($registrations as $registration)
		        {
		            $this->sendRegistrationMail($registration['name'], $registration['email']);
		        }
		        
		    }
		    if(isset($_REQUEST['workshops'])) {
		        if(isset($_REQUEST['confirmuid'])) {
		            $uid = intval($_REQUEST['confirmuid']);
		            $wid = intval($_REQUEST['wid']);
		            $confirm = intval($_REQUEST['confirm']);
		            $this->model->setConfirmedWorkshop($uid, $wid, $confirm);
		        }
		        $this->view->workshop_confirmation_matrix($this->model->listWorkshopWishes(), $this->model->listWorkshops());
		    } else {

				$registrations = $this->model->getRegistrations($this->fields_required);
				array_walk($registrations, function(&$v, $k) {$validationCode = $this->calcValidationCode($v['email']); $link = 'https://foodsharing.de/?page=register&validate='.$v['email'].'&code='.$validationCode; $v['edit'] = "$link";});
	    
		        $this->view->registrationList($registrations);
		    }
		} else
		{
			// Do we have any previous data on the user?
			$fsid_registered = $this->model->fsidIsRegistered(fsid());
			$validation_success = False;
			if(isset($_REQUEST['validate']) && isset($_REQUEST['code']))
			{
				$email = trim($_REQUEST['validate']);
				$code = trim($_REQUEST['code']);
				if($this->calcValidationCode($email) == $code)
				{
					$validation_success = True;
				}
			}

			if($this->myGetPost('form_submit') == 'signup_meeting')
			{
				$this->handleSignup();
				// Dirty... Signup page handles everything itself
				return;
			} elseif($this->myGetPost('form_submit') == 'edit_meeting' && ($fsid_registered || $validation_success))
			{
				$this->handleEdit();
			}
			if($fsid_registered || $validation_success)
			{
				if($validation_success)
				{
					$this->model->setValid($email);
				} else
				{
					$fs = $this->model->getOne_foodsaver(fsId());
					$email = $fs['email'];
				}
				
				$uid = $this->model->getIDbyEmail($email);
				if(isset($_REQUEST['workshops'])) {
				    if($lang == 'de') {
				        $infotext = $this->model->getContent(43);
				    } else {
				        $infotext = $this->model->getContent(44);
				    }
				    if($this->myGetPost('form_submit') == 'register_workshop')
				    {
				        $w1 = $this->myGetPost('wish1');
				        $w2 = $this->myGetPost('wish2');
				        $w3 = $this->myGetPost('wish3');
				        $this->model->updateWorkshopWish($uid, $w1, 1);
				        $this->model->updateWorkshopWish($uid, $w2, 2);
				        $this->model->updateWorkshopWish($uid, $w3, 3);

				    }
				    $workshops = $this->model->listWorkshops();
				    $wishes = $this->model->getWorkshopWishes($uid);
				    foreach ($wishes as $wish) {
				        switch($wish['wish'])
				        {
				            case 1:
				                $g_data['wish1'] = $wish['wid'];
				                break;
                            case 2:
                                $g_data['wish2'] = $wish['wid'];
                                break;
                            case 3:
                                $g_data['wish3'] = $wish['wid'];
                                break;
				        }
				    }
				    
				    $this->view->workshopSignup($workshops, $infotext, $lang);
				} else
				{
    				$registration = $this->model->getRegistrations($this->fields_required, $email);
    				array_walk($registration[0], function($v, $k) { global $g_data; if($k == 'sleep_at' || $k == 'take_part' || $k == 'languages' || $k == 'languages_translate' || $k == 'available_thursday') $g_data[$k] = explode(',', $v); else $g_data[$k] = $v; });
    				// Edit page
    				$this->view->signup($infotext, True);
				}
			} else
			{
				// Signup page
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
				$this->view->signup($infotext, False);
			}
		}
	}

	private function myGetPost($k)
	{
		if(isset($_POST[$k]))
		{
			$v = $_POST[$k];
			if(is_array($v))
			{
				return array_map("trim", $v);
			} else
			{
				return trim($_POST[$k]);
			}
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
			} else
			{
			    $vals[$k] = Null;
			}
			$g_data[$k] = $val;
		}
		return $vals;
	}

	private function validate($edit = False)
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
			$this->view->signupError('err_not_all_req_fields', $edit, implode(', ', array_keys($missing_required_fields)));
		} elseif(!validEmail($fields['email']))
		{
			$this->view->signupError('err_email', $edit);
		} else
		{
			return $fields;
		}
		return False;
	}

	private function handleEdit()
	{
		if($fields = $this->validate(True))
		{
			$this->model->edit($fields, $fields['email']);
		}
	}

	private function handleSignup()
	{
		if($fields = $this->validate(False))
		{
			if($this->model->isIpBlock($_SERVER['REMOTE_ADDR']))
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
			    $this->sendRegistrationMail($fields['name'], $fields['email']);
				$this->view->signupOkay();
			}
		}
	}
}
