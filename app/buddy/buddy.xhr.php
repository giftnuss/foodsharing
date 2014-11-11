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

			return array(
					'status' => 1,
					'script' => '$(".buddyRequest").remove();pulseInfo("Jetzt kennt ihr euch!");init_infos();'
			);
		}
		elseif($this->model->buddyRequest($_GET['id']))
		{	
			
			// language string for title
			$title = 'buddy_request_title';
			
			
			// language string for body too
			$body = 'buddy_request';
			
			
			// icon css class
			$icon = img(S::user('photo'));
			
			
			// whats happen when click on the bell content
			$link_attributes = array('href' => '#', 'onclick' => 'profile('.(int)fsId().');return false;');
			
			
			// variables for the language strings
			$vars = array('name' => S::user('name'));
			
			
			$this->model->addBell($_GET['id'], $title, $body, $icon, $link_attributes, $vars);
			
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