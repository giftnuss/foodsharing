<?php
class StatControl extends Control
{	
	public function __construct()
	{
		
		if(!S::may())
		{
			goLogin();
		}
		else if(!S::may('orga'))
		{
			go('/');
		}
		
		$this->model = new StatModel();
		$this->view = new StatView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		addBread('Statistik Tools');
		addContent($this->view->index());
	}
	
	public function wartung()
	{
		if(isOrgaTeam())
		{
			wartung();
			addContent(v_info('Wartung erledigt!'));
			
			/*mailbox refresh */
			$last_refresh = (int)Mem::get('mailbox_refresh');
				
			$cur_time = (int)time();
				
			if(
					$last_refresh == 0
					||
					($cur_time - $last_refresh) > 30
			)
			{
				Mem::set('mailbox_refresh', $cur_time);
				$xhr = loadXhr('mailbox');
				$xhr->refresh();
			}
			
		}
	}
	
	public function cronjobs()
	{
		cronjobs();
	}
}