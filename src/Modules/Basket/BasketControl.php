<?php

namespace Foodsharing\Modules\Basket;

use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\BasketRequests\Status;

class BasketControl extends Control
{
	private $basketGateway;

	public function __construct(BasketView $view, BasketGateway $basketGateway)
	{
		$this->view = $view;
		$this->basketGateway = $basketGateway;

		parent::__construct();

		$this->func->addBread('Essenskörbe');
	}

	public function index()
	{
		if ($id = $this->uriInt(2)) {
			if ($basket = $this->basketGateway->getBasket($id)) {
				$this->basket($basket);
			}
		} else {
			if ($m = $this->uriStr(2)) {
				if (method_exists($this, $m)) {
					$this->$m();
				} else {
					$this->func->go('/essenskoerbe/find');
				}
			} else {
				$this->func->go('/essenskoerbe/find');
			}
		}
	}

	public function find()
	{
		$baskets = $this->basketGateway->listCloseBaskets($this->session->id(), $this->session->getLocation());
		$this->view->find($baskets, $this->session->getLocation());
	}

	private function basket($basket)
	{
		$wallposts = false;
		$requests = false;

		if ($this->session->may()) {
			if ($basket['fs_id'] != S::id()) {
				$this->func->addJsFunc('
				function u_wallpostReady(postid)
				{
					ajax.req("basket","follow",{
						data:{bid:' . (int)$basket['id'] . '}
					});
				}');
			}
			$wallposts = $this->wallposts('basket', $basket['id']);
			if ($basket['fs_id'] == S::id()) {
				$requests = $this->basketGateway->listRequests($basket['id'], S::id());
			}
		}
		if ($basket['until_ts'] >= time() && $basket['status'] == Status::REQUESTED_MESSAGE_READ) {
			$this->view->basket($basket, $wallposts, $requests);
		} elseif ($basket['until_ts'] <= time() || $basket['status'] == Status::DENIED) {
			$this->view->basketTaken($basket);
		}
	}
}
