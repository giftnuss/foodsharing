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

	// literal constants
	private const TIME_TS = 'time_ts';
	private const DESCRIPTION = 'description';
	private const PICTURE = 'picture';
	private const UPDATED_AT = 'updatedAt';
	private const STATUS = 'status';
	private const CONTACT_TYPES = 'contactTypes';
	private const MOBILE_NUMBER = 'handy';
	private const NOT_LOGGED_IN = 'not logged in';
	private const ID = 'id';
	private const CREATED_AT = 'createdAt';
	private const REQUESTS = 'requests';
	private const LAT = 'lat';
	private const LON = 'lon';
	private const TEL = 'tel';

	public function __construct(BasketGateway $gateway, Session $session)
	{
		$this->gateway = $gateway;
		$this->session = $session;
	}

	/**
	 * Normalizes a basket request.
	 *
	 * @param array $request
	 *
	 * @return array
	 */
	private function normalizeRequest($request): array
	{
		$user = RestNormalization::normalizeFoodsaver($request);

		return [
			'user' => $user,
			'time' => $request[self::TIME_TS],
		];
	}

	/**
	 * Normalizes the details of a basket of the current user for the Rest
	 * response, including requests.
	 *
	 * @param array $b basket data
	 * @param array $updates list of updates
	 */
	private function normalizeMyBasket($b, array $updates = []): array
	{
		$basket = [
			self::ID => (int)$b[self::ID],
			self::DESCRIPTION => html_entity_decode($b[self::DESCRIPTION]),
			self::PICTURE => $b[self::PICTURE],
			self::CREATED_AT => (int)$b[self::TIME_TS],
			self::UPDATED_AT => (int)$b[self::TIME_TS],
			self::REQUESTS => []
		];

		// add requests, if there are any in the updates
		foreach ($updates as $update) {
			if ((int)$update[self::ID] == $basket[self::ID]) {
				$basket[self::REQUESTS][] = $this->normalizeRequest($update);
				$basket[self::UPDATED_AT] = max($basket[self::UPDATED_AT], (int)$update[self::TIME_TS]);
			}
		}

		return $basket;
	}

	/**
	 * Normalizes the details of a basket for the Rest response.
	 *
	 * @param array $b the basket data
	 *
	 * @return array
	 */
	private function normalizeBasket($b): array
	{
		// set main properties
		$creator = RestNormalization::normalizeFoodsaver($b);
		$basket = [
			self::ID => (int)$b[self::ID],
			self::STATUS => (int)$b[self::STATUS],
			self::DESCRIPTION => html_entity_decode($b[self::DESCRIPTION]),
			self::PICTURE => $b[self::PICTURE],
			self::CONTACT_TYPES => array_map('\intval', explode(':', $b['contact_type'])),
			self::CREATED_AT => (int)$b[self::TIME_TS],
			self::UPDATED_AT => (int)$b[self::TIME_TS],
			'until' => (int)$b['until_ts'],
			self::LAT => (float)$b[self::LAT],
			self::LON => (float)$b[self::LON],
			'creator' => $creator
		];

		// add phone numbers if contact_type includes telephone
		$tel = '';
		$handy = '';
		if (isset($b['contact_type']) && \in_array(2, $basket[self::CONTACT_TYPES], true)) {
			$tel = $b[self::TEL];
			$handy = $b[self::MOBILE_NUMBER];
		}
		$basket[self::TEL] = $tel;
		$basket[self::MOBILE_NUMBER] = $handy;

		return $basket;
	}

	/**
	 * Returns a list of all basket IDs together with the coordinates. Returns
	 * 200 or 401, if not logged in.
	 *
	 * @Rest\Get("baskets/coordinates")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getBasketCoordinatesAction(): \Symfony\Component\HttpFoundation\Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$baskets = $this->gateway->getBasketCoordinates();

		return $this->handleView($this->view(['baskets' => $baskets], 200));
	}

	/**
	 * Returns details of the basket with the given ID. Returns 200 and the
	 * basket, 500 if the basket does not exist, or 401 if not logged in.
	 *
	 * @Rest\Get("baskets/basket/{basketId}", requirements={"basketId" = "\d+"})
	 *
	 * @param int $basketId
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getBasketAction($basketId): \Symfony\Component\HttpFoundation\Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$basket = $this->gateway->getBasket($basketId);
		if (!$basket || $basket[self::STATUS] == Status::DELETED_OTHER_REASON) {
			throw new HttpException(404, 'Basket does not exist.');
		} elseif ($basket[self::STATUS] == Status::DELETED_PICKED_UP) {
			throw new HttpException(404, 'Basket was already picked up.');
		}

		$data = $this->normalizeBasket($basket);

		return $this->handleView($this->view(['basket' => $data], 200));
	}

	/**
	 * Lists all baskets of the current user. Returns 200 and a list of
	 * baskets or 401, if not logged in.
	 *
	 * @Rest\Get("baskets/mybaskets")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listMyBasketsAction(): \Symfony\Component\HttpFoundation\Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$updates = $this->gateway->listUpdates($this->session->id());
		$baskets = $this->gateway->listMyBaskets($this->session->id());
		$data = array_map(function ($b) use ($updates) {
			return $this->normalizeMyBasket($b, $updates);
		}, $baskets);

		return $this->handleView($this->view(['baskets' => $data], 200));
	}

	/**
	 * Adds a new basket. The description must not be empty. All other
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
	 * @Rest\RequestParam(name="lifetime", nullable=true, default=7)
	 *
	 * @param ParamFetcher $paramFetcher
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function addBasketAction(ParamFetcher $paramFetcher): \Symfony\Component\HttpFoundation\Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		// prepare and check description
		$description = trim(strip_tags($paramFetcher->get(self::DESCRIPTION)));
		if (empty($description)) {
			throw new HttpException(400, 'The description must not be empty.');
		}

		// find user's location
		$location_type = 0;
		$loc = $this->session->getLocation();
		$lat = $loc[self::LAT];
		$lon = $loc[self::LON];
		if ($lat === 0 && $lon === 0) {
			throw new HttpException(400, 'The user profile has no address.');
		}

		// parse contact type and phone numbers
		$contactString = '1';
		$phone = [
			self::TEL => '',
			self::MOBILE_NUMBER => ''
		];
		$contactTypes = $paramFetcher->get(self::CONTACT_TYPES);
		if ($contactTypes !== null && \is_array($contactTypes)) {
			$contactString = implode(':', $contactTypes);
			if (\in_array(2, $contactTypes, true)) {
				$phone = [
					self::TEL => preg_replace('/[^0-9\-\/]/', '', $paramFetcher->get(self::TEL)),
					self::MOBILE_NUMBER => preg_replace('/[^0-9\-\/]/', '', $paramFetcher->get(self::MOBILE_NUMBER)),
				];
			}
		}

		//check lifetime
		$lifetime = $paramFetcher->get('lifetime');
		if ($lifetime < 1 || $lifetime > 21) {
			$lifetime = 7;
		}

		//add basket
		$basketId = $this->gateway->addBasket($description, '', $phone, $contactString,
				$paramFetcher->get('weight'), $location_type, $lat, $lon,
				$lifetime * 60 * 60 * 24,
				$this->session->user('bezirk_id'), $this->session->id()
		);
		if ($basketId === 0) {
			throw new HttpException(400, 'Unable to create the basket.');
		}

		// add food types
		$foodTypes = $paramFetcher->get('foodTypes');
		if ($foodTypes !== null && \is_array($foodTypes)) {
			$types = array();
			foreach ($foodTypes as $ft) {
				if ((int)$ft > 0) {
					$types[] = (int)$ft;
				}
			}

			$this->gateway->addTypes($basketId, $types);
		}

		// add kinds of food
		$foodKinds = $paramFetcher->get('foodKinds');
		if ($foodKinds !== null && \is_array($foodKinds)) {
			$kinds = array();
			foreach ($foodKinds as $fk) {
				if ((int)$fk > 0) {
					$kinds[] = (int)$fk;
				}
			}

			$this->gateway->addKind($basketId, $kinds);
		}

		// return the created basket
		$data = $this->normalizeBasket($this->gateway->getBasket($basketId));

		return $this->handleView($this->view(['basket' => $data], 200));
	}

	/**
	 * Removes a basket of this user with the given ID. Returns 200 if a basket
	 * of the user was found and deleted, 404 if no such basket was found, or
	 * 401 if not logged in.
	 *
	 * @Rest\Delete("baskets/remove/{basketId}", requirements={"basketId" = "\d+"})
	 *
	 * @param int $basketId
	 *
	 * @return null|\Symfony\Component\HttpFoundation\Response
	 */
	public function removeBasketAction($basketId): ?\Symfony\Component\HttpFoundation\Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		$status = $this->gateway->removeBasket($basketId, $this->session->id());

		if ($status === 0) {
			throw new HttpException(404, 'Basket was not found or cannot be deleted.');
		}

		return $this->handleView($this->view([], 200));
	}
}
