<?php
class DashboardControl extends Control
{	
	private $user;
	public function __construct()
	{
		
		$this->model = new DashboardModel();
		$this->view = new DashboardView();
		
		parent::__construct();
		
		if(!S::may())
		{
			go('/');
		}
		
		$this->user = $this->model->getUser();
	}
	
	public function index()
	{
		addBread(s('dashbaord'));
		addTitle(s('dashbaord'));
		/*
		 * User is foodsaver
		 */
		
		if($this->user['rolle'] > 0 && !getBezirkId())
		{
			addJs('becomeBezirk();');
		}
		
		// foodsharer dashboard
		$this->dashFs();
		
	}
	
	public function dashFs()
	{
		
		$this->setContentWidth(8, 8);
		$subtitle = s('no_saved_food');
		
		if($this->user['stat_fetchweight'] > 0)
		{
			$subtitle = sv('saved_food',array('weight' => $this->user['stat_fetchweight']));
		}
		
		addContent(
			$this->view->topbar(
			sv('welcome',array('name'=>$this->user['name'])),
				$subtitle,
				avatar($this->user,50,'/img/fairteiler50x50.png')
			),
			CNT_TOP
		);
		
		addContent(v_berlinevent());
		
		addContent($this->view->foodsharerMenu(),CNT_LEFT);
		
		$this->view->updates();
		
		if($this->user['lat'] && ($baskets = $this->model->listCloseBaskets(50)))
		{
			addContent($this->view->closeBaskets($baskets),CNT_LEFT);
		}
		else
		{
			if($baskets = $this->model->getNewestFoodbaskets())
			{
				addContent($this->view->newBaskets($baskets),CNT_LEFT);
			}
		}
	}
}