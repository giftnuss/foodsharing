<?php 
class FsbetriebXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new FsbetriebModel();
		$this->view = new FsbetriebView();

		parent::__construct();
	}
}