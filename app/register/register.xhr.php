<?php 
class RegisterXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new RegisterModel();
		$this->view = new RegisterView();

		parent::__construct();
	}
}