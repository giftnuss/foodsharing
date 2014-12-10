<?php
class BasketControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new BasketModel();
		$this->view = new BasketView();
		
		parent::__construct();
		
		addBread('Essenkörbe');
		
	}
	
	public function index()
	{
		if($id = $this->uriInt(2))
		{
			if($basket = $this->model->getBasket($id))
			{
				$this->basket($basket);
			}
		}
	}
	
	private function basket($basket)
	{
		$wallposts = false;
		$requests = false;
		
		if(S::may())
		{
			$wallposts = $this->wallposts('basket', $basket['id']);
			if($basket['fs_id'] == fsId())
			{
				$requests = $this->model->listRequests($basket['id']);
			}
		}
		$this->view->basket($basket,$wallposts,$requests);
		
	}
}