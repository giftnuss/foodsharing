<?php 
class BuddyXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new BuddyModel();
		$this->view = new BuddyView();

		parent::__construct();
	}
	
	public function request()
	{
		if($this->model->buddyRequestedMe($_GET['id']))
		{
			$this->model->confirmBuddy($_GET['id']);
			cronjobs_daily($_GET['id']);
			cronjobs_daily(fsId());
			return array(
					'status' => 1,
					'script' => '$(".buddyRequest").remove();pulseInfo("Jetzt kennt ihr euch!");init_infos();'
			);
		}
		elseif($this->model->buddyRequest($_GET['id']))
		{
			return array(
				'status' => 1,
				'script' => '$(".buddyRequest").remove();pulseInfo("Anfrage versendet!");'
			);
		}
	}
	
	public function removeRequest()
	{
		$this->model->removeRequest($_GET['id']);
		
		return array(
			'status' => 1,
			'script' => 'pulseInfo("Anfrage gelöscht");$(".buddyreq-'.(int)$_GET['id'].'").remove();'		
		);
	}
}