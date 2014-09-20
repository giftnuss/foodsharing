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
		
	}
}