<?php 
class ActivityXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new ActivityModel();
		$this->view = new ActivityView();

		parent::__construct();
	}
	
	public function loadmore()
	{
		$xhr = new Xhr();
		
		/*
		 * get FOrum updates
		*/
		
		$updates = array();
		if($up = $this->model->loadForumUpdates($_GET['page']))
		{
			$updates = $up;
				
			if($up = $this->model->loadBetriebUpdates($_GET['page']))
			{
				$updates = array_merge($updates,$up);
			}
			if($up = $this->model->loadMailboxUpdates($_GET['page']))
			{
				$updates = array_merge($updates,$up);
			}
		}
		
		$xhr->addData('updates', $updates);
		
		$xhr->send();
	}
	
	public function load()
	{
		/*
		 * get FOrum updates
		 */
		
		$xhr = new Xhr();
		$updates = array();
		if($up = $this->model->loadForumUpdates())
		{			
			$updates = $up;
			
			if($up = $this->model->loadBetriebUpdates())
			{
				$updates = array_merge($updates,$up);
			}
			if($up = $this->model->loadMailboxUpdates())
			{
				$updates = array_merge($updates,$up);
			}
		}
		
		$xhr->addData('updates', $updates);
		
		$xhr->send();
	}
}