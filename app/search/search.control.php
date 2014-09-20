<?php
class SearchControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new SearchModel();
		$this->view = new SearchView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		
	}
}