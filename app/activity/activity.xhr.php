<?php 
class ActivityXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new ActivityModel();
		$this->view = new ActivityView();

		parent::__construct();
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
		
		ksort($updates);
		
		$out = array();
		foreach ($updates as $u)
		{
			$out[] = $u;
		}
		
		$xhr->addData('updates', $out);
		
		$xhr->send();
	}
}