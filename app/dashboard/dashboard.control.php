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
		$is_bot = S::may('bot');
		$is_fs = S::may('fs');
		
		if(isset($_SESSION['client']['betriebe']) && is_array($_SESSION['client']['betriebe']) && count($_SESSION['client']['betriebe']) > 0)
		{
			$is_fs = true;
		}
		
		if(isset($_SESSION['client']['verantwortlich']) && is_array($_SESSION['client']['verantwortlich']) && count($_SESSION['client']['verantwortlich']) > 0)
		{
			$is_bieb = true;
		}
		
		if(isset($_SESSION['client']['botschafter']) && is_array($_SESSION['client']['botschafter']) && count($_SESSION['client']['botschafter']) > 0)
		{
			$is_bieb = true;
		}
		
		if(
			(
				$is_fs
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
				$is_bot
				&&
				(int)$this->model->qOne('SELECT COUNT(id) FROM fs_quiz_session WHERE quiz_id = 3 AND status = 1 AND foodsaver_id = '.(int)fsId()) == 0
			)
		)
		{
			$check = true;
		}
		
		if(true)
		{
			$cnt = $this->model->getContent(33);
			
			$cnt['body'] = str_replace(array(
				'{NAME}',
				'{ANREDE}'
			),array(
				S::user('name'),
				s('anrede_'.S::user('gender'))
			),$cnt['body']);
			
			if(S::option('quiz-infobox-seen'))
			{
				$cnt['body'] = '<div>' . substr(strip_tags($cnt['body']),0,120) . ' ...<a href="#" onclick="$(this).parent().hide().next().show();return false;">weiterlesen</a></div><div style="display:none;">'.$cnt['body'].'</div>';
			}
			else 
			{
				$cnt['body'] = $cnt['body'].'<p><a href="#"onclick="$(this).parent().parent().hide();ajax.req(\'quiz\',\'hideinfo\');return false;"><i class="fa fa-check-square-o"></i> Hinweis gelesen und nicht mehr anzeigen</a></p>';
			}
			
			
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