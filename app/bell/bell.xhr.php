<?php 
class BellXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new BellModel();
		$this->view = new BellView();

		parent::__construct();
	}
	
	/**
	 * ajax call to refresh infobar messages
	 */
	public function infobar()
	{
		S::noWrite();
		
		$xhr = new Xhr();
		$bells = $this->model->listBells(20);
		$xhr->addData('html', $this->view->bellList($bells));
		
		$xhr->send();
	}
	
	/**
	 * ajax call to delete an bell
	 */
	public function delbell()
	{
		$this->model->delbell($_GET['id']);
	}
}