<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Basket\BasketGateway;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Rest controller for food baskets.
 */
class BasketRestController extends FOSRestController
{
	private $gateway;
	private $session;

	public function __construct(BasketGateway $gateway, Session $session)
	{
		$this->gateway = $gateway;
		$this->session = $session;
	}

	/**
	 * Normalizes the details of a basket for the Rest response.
	 */
	private function normalizeBasket($b, $updates = [])
	{
		//set main properties
		$basket = [
			'id' => (int)$b['id'],
			'description' => html_entity_decode($b['description']),
			'createdAt' => date('Y-m-d\TH:i:s', $b['time_ts']),
			'updatedAt' => date('Y-m-d\TH:i:s', $b['time_ts']),
			'requests' => []
		];

		//add requests, if there are any in the updates
		foreach ($updates as $update) {
			if ((int)$update['id'] == $basket['id']) {
				$time = date('Y-m-d\TH:i:s', $update['time_ts']);
				$basket['requests'][] = [
					'user' => [
						'id' => (int)$update['fs_id'],
						'name' => $update['fs_name'],
						'avatar' => $update['fs_photo'],
						'sleepStatus' => $update['sleep_status'],
					],
					'description' => $update['description'],
					'time' => $time,
				];
				if (strcmp($time, $basket['updatedAt']) > 0) {
					$basket['updatedAt'] = $time;
				}
			}
		}

		return $basket;
	}

	/**
	 * Returns a list of all basket IDs together with the coordinates.
	 *
	 * @Rest\Get("baskets/coordinates")
	 */
	public function getBasketsCoordsAction()
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		$baskets = $this->gateway->getBasketCoordinates();

		$view = $this->view([
			'data' => ['baskets' => $baskets]
		], 200);

		return $this->handleView($view);
	}

	/**
	 * Returns details of the basket with the given ID.
	 *
	 * @Rest\Get("baskets/basket/{basketId}", requirements={"basketId" = "\d+"})
	 */
	public function getBasketAction($basketId)
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		//TODO: this throws (500 "Notice: Undefined index: foodsaver_id") instead of a 400 code
		$basket = $this->gateway->getBasket($basketId);
		$updates = $this->gateway->listUpdates($this->session->id());
		$basket = $this->normalizeBasket($basket, $updates);

		$view = $this->view([
			'data' => ['basket' => $basket]
		], 200);

		return $this->handleView($view);
	}
}
