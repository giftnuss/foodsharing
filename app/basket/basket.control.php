<?php
class BasketControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new BasketModel();
		$this->view = new BasketView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		
	}
}