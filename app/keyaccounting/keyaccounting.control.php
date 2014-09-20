<?php
class KeyaccountingControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new KeyaccountingModel();
		$this->view = new KeyaccountingView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		
	}
}