<?php
class ContentControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new ContentModel();
		$this->view = new ContentView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		
	}
	
	public function partner()
	{
		if($cnt = $this->model->getContent(10))
		{
			addBread($cnt['title']);
			addTitle($cnt['title']);
			
			addContent($this->view->partner($cnt));
		}
	}
	
	public function about()
	{
		if($cnt = $this->model->getContent(9))
		{
			addBread($cnt['title']);
			addTitle($cnt['title']);
				
			addContent($this->view->about($cnt));
		}
	}
}