<?php
class ProfileControl extends Control
{	
	private $foodsaver;
	private $stats;
	
	public function __construct()
	{
		
		$this->model = new ProfileModel();
		$this->view = new ProfileView();
		
		
		
		parent::__construct();
		
		if(isset($_GET['id']))
		{
			$this->model->setFsId((int)$_GET['id']);
			$this->fs_id = (int)$_GET['id'];
			if($data = $this->model->getProfile())
			{
				$this->foodsaver = $data;
				$this->foodsaver['buddy'] = $this->model->buddyStatus($this->foodsaver['id']);
			}
			else
			{
				goPage('dashboard');
			}
		}
		else
		{
			goPage('dashboard');
		}
		
	}
	
	public function index()
	{
		addBread($this->foodsaver['name'].' '.$this->foodsaver['nachname']);
		addContent(
			'<div class="ui-widget ui-widget-content ui-corner-all margin-bottom">
				'.$this->wallposts('foodsaver',$this->foodsaver['id']).'
			</div>'
		);
	}
}