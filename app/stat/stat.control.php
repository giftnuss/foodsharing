<?php
class StatControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new StatModel();
		$this->view = new StatView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		addBread('Statistik Tools');
		addContent($this->view->index());
		
		addJs('
			setTimeout(function(){
				clearTimeout(g_chatheartbeatTO);
				clearInterval(g_interval_newBasket);
				showLoader();
			},5000);		
		');
	}
	
	public function wartung()
	{
		if(isOrgaTeam())
		{
			wartung();
			addContent(v_info('Wartung erledigt!'));
			
			/*mailbox refresh */
			$last_refresh = (int)$this->store->get('mailbox_refresh');
				
			$cur_time = (int)time();
				
			$this->updateMumble($pass);
				
			if(
					$last_refresh == 0
					||
					($cur_time - $last_refresh) > 30
			)
			{
				$this->store->put('mailbox_refresh', $cur_time);
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