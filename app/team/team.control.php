<?php
class TeamControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new TeamModel();
		$this->view = new TeamView();
		
		parent::__construct();
		
		addScript('/js/jquery.qrcode.min.js');
	}
	
	public function index()
	{
		addBread(s('team'),'/team');
		addTitle(s('team'));
		
		
		
		if($id = $this->uriInt(1))
		{
			if($user = $this->model->getUser($id))
			{
				addTitle($user['name']);
				addBread($user['name']);
				addContent($this->view->user($user));
				
				addContent($this->view->contactForm($user));
			}
			else
			{
				go('/team');
			}
		}
		else if($team = $this->model->getTeam())
		{
			addContent($this->view->teamlist($team));
		}
	}
}