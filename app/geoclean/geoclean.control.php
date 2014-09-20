<?php
class GeocleanControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new GeocleanModel();
		$this->view = new GeocleanView();
		
		parent::__construct();
		
	}
	
	public function index()
	{		
		addBread('Geo Location Cleaner');
		
		if($foodsaver = $this->model->getFsWithoutGeo())
		{
			addContent($this->view->listFs($foodsaver));
		}
		
		addContent($this->view->rightmenu(),CNT_RIGHT);
	}
}