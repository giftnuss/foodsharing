<?php
class TemplatesControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new TemplatesModel();
		$this->view = new TemplatesView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		
	}
}