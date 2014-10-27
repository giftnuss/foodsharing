<?php
class StatisticsControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new StatisticsModel();
		$this->view = new StatisticsView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		
		$content = $this->model->getContent(11);
		
		addTitle($content['title']);
		
		
		
		$stat_gesamt = $this->model->getStatGesamt();
		
		$stat_cities = $this->model->getStatCities();
		
		foreach ($stat_cities as $i => $c)
		{
			$stat_cities[$i]['percent'] = $this->getPercent($stat_gesamt['fetchweight'],$c['fetchweight']);
		}
		
		addContent($this->view->getStatCities($stat_cities),CNT_RIGHT);
		addContent($this->view->getStatGesamt($stat_gesamt),CNT_LEFT);
		
		$content = str_replace(
				'{STAT_GESAMT}', 
				$stat_gesamt, 
				$content['body']
		);
		
		addContent($content,CNT_LEFT);
		
		$stat_fs = $this->model->getStatFoodsaver();
		
		addContent($this->view->getStatFoodsaver($stat_fs),CNT_RIGHT);
	}
	
	private function getPercent($gesamt,$teil)
	{
		if($gesamt)
		{
			return  round(($teil / ($gesamt / 100)),0);
		}
		return 0;
	}
}