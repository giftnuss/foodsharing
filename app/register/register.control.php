<?php
class RegisterControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new RegisterModel();
		$this->view = new RegisterView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		global $g_data;
		$lang = 'de';
		if($_GET['lang'] == 'en') {
			$lang = 'en';
		}
		if($lang == 'de') {
			$infotext = $this->model->getContent(40);
		} else {
			$infotext = $this->model->getContent(41);
		}
		$foodsaver = array();
		if(S::may()) {
			$foodsaver['rolle'] = $fs['rolle'];
			$g_data['name'] = $fs['name'].' '.$fs['nachname'];
			$g_data['geb_datum'] = $fs['geb_datum'];
			$g_data['address'] = $fs['anschrift'].' '.$fs['plz'];
			$g_data['ort'] = $fs['stadt'];
			$g_data['email'] = $fs['email'];
			$g_data['phone'] = $fs['handy'];
		}
		$this->view->signup($infotext, $foodsaver);
	}
}
