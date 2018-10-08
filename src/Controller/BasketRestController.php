<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Basket\BasketGateway;
use Foodsharing\Modules\Core\DBConstants\BasketRequests\Status;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
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
	 * Normalizes a basket request.
	 */
	private function normalizeRequest($request)
	{
		$user = RestNormalization::normalizeFoodsaver($request);

		return [
			'user' => $user,
			'time' => $request['time_ts'],
		];
	}

	/**
	 * Normalizes the details of a basket of the current user for the Rest
	 * response, including requests.
	 */
	private function normalizeMyBasket($b, $updates = [])
	{
		$basket = [
			'id' => (int)$b['id'],
			'description' => html_entity_decode($b['description']),
			'picture' => $b['picture'],
			'createdAt' => (int)$b['time_ts'],
			'updatedAt' => (int)$b['time_ts'],
			'requests' => []
		];

		//add requests, if there are any in the updates
		foreach ($updates as $update) {
			if ((int)$update['id'] == $basket['id']) {
				$basket['requests'][] = $this->normalizeRequest($update);
				$basket['updatedAt'] = max($basket['updatedAt'], (int)$update['time_ts']);
			}
		}

		return $basket;
	}

	/**
	 * Normalizes the details of a basket for the Rest response.
	 */
	private function normalizeBasket($b)
	{
		//set main properties
		$creator = RestNormalization::normalizeFoodsaver($b);
		$basket = [
			'id' => (int)$b['id'],
			'status' => (int)$b['status'],
			'description' => html_entity_decode($b['description']),
			'picture' => $b['picture'],
			'contactTypes' => array_map('intval', explode(':', $b['contact_type'])),
			'createdAt' => (int)$b['time_ts'],
			'updatedAt' => (int)$b['time_ts'],
			'until' => (int)$b['until_ts'],
			'lat' => (float)$b['lat'],
			'lon' => (float)$b['lon'],
			'creator' => $creator
		];

		//add phone numbers if contact_type includes telephone
		$tel = '';
		$handy = '';
		if (isset($b['contact_type'])) {
			if (in_array(2, $basket['contactTypes'])) {
				$tel = $b['tel'];
				$handy = $b['handy'];
			}
		}
		$basket['tel'] = $tel;
		$basket['handy'] = $handy;

		return $basket;
	}

	/**
	 * Returns a list of all basket IDs together with the coordinates. Returns
	 * 200 or 401, if not logged in.
	 *
	 * @Rest\Get("baskets/coordinates")
	 */
	public function getBasketCoordinatesAction()
	{
		if (!$this->session->may()) {
			throw new HttpException(401, 'not logged in');
		}

		$baskets = $this->gateway->getBasketCoordinates();

		return $this->handleView($this->view(['baskets' => $baskets], 200));
	}

	/**
	 * Returns details of the basket with the given ID. Returns 200 and the
	 * basket, 500 if the basket does not exist, or 401 if not logged in.
	 *
	 * @Rest\Get("baskets/basket/{basketId}", requirements={"basketId" = "\d+"})
	 */
	public function getBasketAction($basketId)
	{
		if (!$this->session->may()) {
			throw new HttpException(401, 'not logged in');
		}

		$basket = $this->gateway->getBasket($basketId);
		if (!$basket || $basket['status'] == Status::DELETED_OTHER_REASON) {
			throw new HttpException(404, 'basket does not exist');
		} elseif ($basket['status'] == Status::DELETED_PICKED_UP) {
			throw new HttpException(404, 'basket was already picked up');
		}

		$data = $this->normalizeBasket($basket);

		return $this->handleView($this->view(['basket' => $data], 200));
	}

	/**
	 * Lists all baskets of the current user. Returns 200 and a list of
	 * baskets or 401, if not logged in.
	 *
	 * @Rest\Get("baskets/mybaskets")
	 */
	public function listMyBasketsAction()
	{
		if (!$this->session->may()) {
			throw new HttpException(401, 'not logged in');
		}

		$updates = $this->gateway->listUpdates($this->session->id());
		$baskets = $this->gateway->listMyBaskets($this->session->id());
		$data = array_map(function ($b) use ($updates) {
			return $this->normalizeMyBasket($b, $updates);
		}, $baskets);

		return $this->handleView($this->view(['baskets' => $data], 200));
	}

	/**
	 * Adds a new basket. The description must not be empty, all other
	 * parameters are optional. Returns the created basket.
	 *
	 * @Rest\Post("baskets/add")
	 * @Rest\RequestParam(name="description", nullable=false)
	 * @Rest\RequestParam(name="contactTypes", nullable=true)
	 * @Rest\RequestParam(name="tel", nullable=true)
	 * @Rest\RequestParam(name="handy", nullable=true)
	 * @Rest\RequestParam(name="weight", nullable=true)
	 * @Rest\RequestParam(name="foodTypes", nullable=true)
	 * @Rest\RequestParam(name="foodKinds", nullable=true)
	 */
	public function addBasketAction(ParamFetcher $paramFetcher)
	{
		if (!$this->session->may()) {
			throw new HttpException(401, 'not logged in');
		}

		//prepare and check description
		$description = trim(strip_tags($paramFetcher->get('description')));
		if (empty($description)) {
			throw new HttpException(400, 'the description must not be empty');
		}

		//find user's location
		$location_type = 0;
		$loc = $this->session->getLocation();
		$lat = $loc['lat'];
		$lon = $loc['lon'];
		if ($lat == 0 && $lon == 0) {
			throw new HttpException(400, 'the user profile has no address');
		}

		//parse contact type and phone numbers
		$contactString = '1';
		$phone = [
			'tel' => '',
			'handy' => ''
		];
		$contactTypes = $paramFetcher->get('contactTypes');
		if (!is_null($contactTypes) && is_array($contactTypes)) {
			$contactString = implode(':', $contactTypes);
			if (in_array(2, $contactTypes)) {
				$phone = [
					'tel' => preg_replace('/[^0-9\-\/]/', '', $paramFetcher->get('tel')),
					'handy' => preg_replace('/[^0-9\-\/]/', '', $paramFetcher->get('handy')),
				];
			}
		}

		//add basket
		$pic = ''; //TODO
		$basketId = $this->gateway->addBasket($description, $pic, $phone, $contactString,
				$paramFetcher->get('weight'), $location_type, $lat, $lon,
				$this->session->user('bezirk_id'), $this->session->id()
		);
		if ($basketId == 0) {
			throw new HttpException(400, 'unable to create the basket');
		}

		//add food types
		$foodTypes = $paramFetcher->get('foodTypes');
		if (!is_null($foodTypes) && is_array($foodTypes)) {
			$types = array();
			foreach ($foodTypes as $ft) {
				if ((int)$ft > 0) {
					$types[] = (int)$ft;
				}
			}

			$this->gateway->addTypes($basketId, $types);
		}

		//add kinds of food
		$foodKinds = $paramFetcher->get('foodKinds');
		if (!is_null($foodKinds) && is_array($foodKinds)) {
			$kinds = array();
			foreach ($foodKinds as $fk) {
				if ((int)$fk > 0) {
					$kinds[] = (int)$fk;
				}
			}

			$this->gateway->addKind($basketId, $kinds);
		}

		//return the created basket
		$data = $this->normalizeBasket($this->gateway->getBasket($basketId));

		return $this->handleView($this->view(['basket' => $data], 200));
	}

	/**
	 * Removes a basket of this user with the given ID. Returns 200 if a basket
	 * of the user was found and deleted, 404 if no such basket was found, or
	 * 401 if not logged in.
	 *
	 * @Rest\Delete("baskets/remove/{basketId}", requirements={"basketId" = "\d+"})
	 */
	public function removeBasketAction($basketId)
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		$status = $this->gateway->removeBasket($basketId, $this->session->id());

		if ($status == 0) {
			throw new HttpException(404, 'basket was not found or cannot be deleted');
		} else {
			return $this->handleView($this->view([]), 200);
		}
	}
}
