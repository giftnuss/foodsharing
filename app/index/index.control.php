<?php
class IndexControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new IndexModel();
		$this->view = new IndexView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		addScript('/js/slippry/slippry.min.js');
		addCss('/js/slippry/slippry.css');
		
		$articles = array();
		
		if(!S::may())
		{
			$articles[] = $this->view->joinIndex();
		}
		
		if($news = $this->view->newsSlider($this->model->latestNews()))
		{
			$articles = array_merge($articles,$news);
		}
		addContent($this->view->printSlider($articles),CNT_OVERTOP);
		
		//addContent('Hallo Foodsharing-Welt');
	}
}