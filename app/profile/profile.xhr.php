<?php 
class ProfileXhr extends Control
{
	private $foodsaver;
	private $model;
	private $view;
	
	public function __construct()
	{
		if(!S::may())
		{
			return array(
					'status' => 1,
					'script' => 'login();'
			);
		}
		$this->model = new ProfileModel();
		$this->view = new ProfileView();

		parent::__construct();
		
		if(isset($_GET['id']))
		{
			$this->model->setFsId($_GET['id']);
			if($fs = $this->model->getData($_GET['id']))
			{
				$this->foodsaver = $fs;
				/*
				 * -1: no buddy 
				 *  0: requested
				 *  1: buddy
				 */
				$this->foodsaver['buddy'] = $this->model->buddyStatus($this->foodsaver['id']);
				
				$this->view->setData($this->foodsaver);
			}
		}
	}
	
	public function rate()
	{
		$rate = 1;
		if(isset($_GET['rate']))
		{
			$rate = (int)$_GET['rate'];
		}
		
		$fsid = (int)$_GET['id'];
		
		if($fsid > 0)
		{		
			$type = (int)$_GET['type'];
			
			$message = '';
			if(isset($_GET['message']))
			{
				$message = strip_tags($_GET['message']);
			}
			
			if(strlen($message) < 100)
			{
				return array(
					'status' => 1,
					'script' => 'pulseError("Bitte gebe mindestens einen 100 Zeichen langen Text zu Deiner Banane ein.");'
				);
			}
			
			$this->model->rate($fsid,$rate,$type,$message);

			$comment = '';
			if($msg = $this->model->getRateMessage($fsid))
			{
				$comment = $msg;
			}
			
			return array(
				'status' => 1,
				'comment' => $comment,
				'title' => 'Nachricht hinterlassen',
				'script' => '$("#fs-profile-rate-comment").dialog("close");$(".vouch-banana").tooltip("close");pulseInfo("Banane wurde gesendet!");profile('.(int)$fsid.');'
			);
		}
	}
	
	public function quickprofile()
	{
		if(!is_object($this->model))
		{
			$this->model = new ProfileModel();
			$this->view = new ProfileView();
		}
		
		$bezirk = $this->model->getBezirk($this->foodsaver['bezirk_id']);
		
		//print_r($this->foodsaver);
		
		$subtitle = '';
		if($this->foodsaver['botschafter'])
		{
			$subtitle = 'ist '.genderWord($this->foodsaver['geschlecht'], 'Botschafter', 'Botschafterin', 'Botschafter/in').' f&uuml;r ';
			foreach ($this->foodsaver['botschafter'] as $i => $b)
			{
				$sep = ', ';
					
				if($i == (count($this->foodsaver['botschafter'])-2))
				{
					$sep = ' und ';
				}
					
				$subtitle .= $b['name'].$sep;
			}
		
			$subtitle = substr($subtitle, 0,(strlen($subtitle)-2));
			if($this->foodsaver['orgateam'] == 1)
			{
				$subtitle .= ', außerdem engagiert '.genderWord($this->foodsaver['geschlecht'], 'er', 'sie', 'er/sie').' sich im Foodsharing Orgateam';
			}
		}
		elseif($this->foodsaver['bezirk_id'] == 0)
		{
			$subtitle = 'hat sich bisher für keinen Bezirk entschieden.';
		}
		else
		{
			$subtitle = 'ist '.genderWord($this->foodsaver['geschlecht'], 'Foodsaver', 'Foodsaverin', 'Foodsaver').' für '.$bezirk['name'];
		}
		
		$photo = img($this->foodsaver['photo'],130,'q');
		
		return array(
				'status' => 1,
				'html' => $this->view->quickprofile($subtitle),
				'script' => ''
		);
	}
}