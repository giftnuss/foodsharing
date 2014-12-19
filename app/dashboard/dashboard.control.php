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
		
		addContent($this->view->foodsharerMenu(),CNT_LEFT);
		
		$check = false;
		
		$is_bieb = S::may('bieb');
		
		if(
			(
				S::may('fs')
				&&
				(int)$this->model->qOne('SELECT COUNT(id) FROM fs_quiz_session WHERE quiz_id = 1 AND status = 1 AND foodsaver_id = '.(int)fsId()) == 0
			)
			||
			(
				$is_bieb
				&&
				(int)$this->model->qOne('SELECT COUNT(id) FROM fs_quiz_session WHERE quiz_id = 2 AND status = 1 AND foodsaver_id = '.(int)fsId()) == 0
			)
			||
			(
				S::may('bot')
				&&
				(int)$this->model->qOne('SELECT COUNT(id) FROM fs_quiz_session WHERE quiz_id = 3 AND status = 1 AND foodsaver_id = '.(int)fsId()) == 0
			)
		)
		{
			$check = true;
		}
		
		if($check)
		{
			$cnt = $this->model->getContent(33);
			
			$cnt['body'] = '<div>' . substr(strip_tags($cnt['body']),0,120) . ' ...<a href="#" onclick="$(this).parent().hide().next().show();return false;">weiterlesen</a></div><div>'.$cnt['body'].'</div>';
			
			addContent(v_info($cnt['body'],$cnt['title']));	
		}
		
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