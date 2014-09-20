<?php
class EmailControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new EmailModel();
		$this->view = new EmailView();
		
		parent::__construct();
		
	}
}