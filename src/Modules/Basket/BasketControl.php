<?php

namespace Foodsharing\Modules\Basket;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;

class BasketControl extends Control
{
	private $gateway;

	public function __construct(BasketModel $model, BasketGateway $gateway, BasketView $view)
	{
		$this->gateway = $gateway;
		$this->model = $model;
		$this->view = $view;

		parent::__construct();

		$this->func->addBread('Essenskörbe');
	}

	public function index()
	{
		if ($id = $this->uriInt(2)) {
			if ($basket = $this->gateway->getBasket($id)) {
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
		$baskets = $this->model->closeBaskets();
		$this->view->find($baskets, S::getLocation($this->model));
	}

	private function basket($basket)
	{
		$wallposts = false;
		$requests = false;

		if (S::may()) {
			if ($basket['fs_id'] != $this->func->fsId()) {
				$this->func->addJsFunc('
				function u_wallpostReady(postid)
				{
					ajax.req("basket","follow",{
						data:{bid:' . (int)$basket['id'] . '}
					});
				}');
			}
			$wallposts = $this->wallposts('basket', $basket['id']);
			if ($basket['fs_id'] == $this->func->fsId()) {
				$requests = $this->gateway->listRequests($basket['id'], S::id());
			}
		}
		if ($basket['until_ts'] >= time() && $basket['status'] == 1) {
			$this->view->basket($basket, $wallposts, $requests);
		} elseif ($basket['until_ts'] <= time() || $basket['status'] == 3) {
			$this->view->basketTaken($basket);
		}
	}
}
