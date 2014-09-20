<?php
class FsbetriebControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new FsbetriebModel();
		$this->view = new FsbetriebView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		if(isset($_GET['id']) && (int)$_GET['id'] > 0)
		{
			addBread(s('betrieb_bread'),'?page=fsbetrieb');
			addStyle('textarea.comment{width:475px}.button{margin-right:8px;}#right .tagedit-list{width:256px;}#foodsaver-wrapper{padding-top:0px;}');
			global $g_data;
			
			$this->handleUpdates();
		}
	}
	
	public function handleUpdates()
	{
		if(isset($_POST['form_submit']) && $_POST['form_submit'] == 'team')
		{
			if($_POST['form_submit'] == 'zeiten')
			{
				$this->updateZeiten();
			}
			else
			{
				$this->updateTeam();
			}
			info(s('changes_saved'));
			clearPost();
		}
		else if(isset($_POST['form_submit']) && $_POST['form_submit'] == 'changestatusform')
		{
			$db->changeBetriebStatus($_GET['id'],$_POST['betrieb_status_id']);
			go(getSelf());
		}
		
	}
	
	public function updateTeam()
	{
		handleTagselect('foodsaver');
		
		if(!empty($g_data['foodsaver']))
		{
			$db->addBetriebTeam($_GET['id'],$g_data['foodsaver'],$g_data['verantwortlicher']);
		}
		else
		{
			info(s('team_not_empty'));
		}
	}
	
	public function updateZeiten()
	{
		$range = range(0,6);
		global $g_data;
		$db->clearAbholer($_GET['id']);
		foreach ($range as $r)
		{
		
			if(isset($_POST['dow'.$r]))
			{
				handleTagselect('dow'.$r);
				foreach ($g_data['dow'.$r] as $fs_id)
				{
					$db->addAbholer($_GET['id'],$fs_id,$r);
				}
		
			}
		}
	}
}