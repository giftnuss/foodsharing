<?php 
class LoginXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new LoginModel();
		$this->view = new LoginView();

		parent::__construct();
	}
}