<?php 
class CoreXhr extends Control
{
	public function __construct()
	{
		$this->model = new Model();
		$this->view = new View();

		parent::__construct();
	}
	
	
}