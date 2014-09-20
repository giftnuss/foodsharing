<?php 
class TemplatesXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new TemplatesModel();
		$this->view = new TemplatesView();

		parent::__construct();
	}
	
	public function templates()
	{
		$templates = $this->model->getTemplates();
		
		header('Content-Type: application/json');
		echo json_encode($templates);
		exit();
	}
}