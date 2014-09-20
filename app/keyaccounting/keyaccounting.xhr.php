<?php 
class KeyaccountingXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new KeyaccountingModel();
		$this->view = new KeyaccountingView();

		parent::__construct();
	}
}