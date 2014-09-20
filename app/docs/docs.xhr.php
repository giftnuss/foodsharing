<?php 
class DocsXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new DocsModel();
		$this->view = new DocsView();

		parent::__construct();
	}
}