<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Basket\BasketGateway;
use Foodsharing\Modules\Core\DBConstants\BasketRequests\Status;
use Foodsharing\Services\BasketService;
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
	private $service;
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

	public function __construct(BasketGateway $gateway, BasketService $service, Session $session)
	{
		$this->gateway = $gateway;
		$this->service = $service;
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
		$user = RestNormalization::normalizeFoodsaver($request, 'fs_');

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
		$creator = RestNormalization::normalizeFoodsaver($b, 'fs_');
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
	 * Checks if the number is a valid value in the given range.
	 */
	private function isValidNumber($value, $lowerBound, $upperBound): bool
	{
		return !is_null($value) && !is_nan($value)
			&& ($lowerBound <= $value) && ($upperBound >= $value);
	}

	/**
	 * Returns a list of baskets depending on the type.
	 * 'mine': lists all baskets of the current user.
	 * 'coordinates': lists all basket IDs together with the coordinates.
	 *
	 * Returns 200 and a list of baskets or 401 if not logged in.
	 *
	 * @Rest\Get("baskets")
	 * @Rest\QueryParam(name="type", requirements="(mine|coordinates)", default="mine")
	 *
	 * @param ParamFetcher $paramFetcher
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listBasketsAction(ParamFetcher $paramFetcher): \Symfony\Component\HttpFoundation\Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		switch ($paramFetcher->get('type')) {
			case 'mine':
				$updates = $this->gateway->listUpdates($this->session->id());
				$baskets = $this->gateway->listMyBaskets($this->session->id());
				$data = array_map(function ($b) use ($updates) {
					return $this->normalizeMyBasket($b, $updates);
				}, $baskets);
				break;
			case 'coordinates':
				$data = $this->gateway->getBasketCoordinates();
				break;
		}

		return $this->handleView($this->view(['baskets' => $data], 200));
	}

	/**
	 * Returns details of the basket with the given ID. Returns 200 and the
	 * basket, 500 if the basket does not exist, or 401 if not logged in.
	 *
	 * @Rest\Get("baskets/{basketId}", requirements={"basketId" = "\d+"})
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
	 * Adds a new basket. The description must not be empty. All other
	 * parameters are optional. Returns the created basket.
	 *
	 * @Rest\Post("baskets")
	 * @Rest\RequestParam(name="description", nullable=false)
	 * @Rest\RequestParam(name="contactTypes", nullable=true)
	 * @Rest\RequestParam(name="tel", nullable=true)
	 * @Rest\RequestParam(name="handy", nullable=true)
	 * @Rest\RequestParam(name="weight", nullable=true)
	 * @Rest\RequestParam(name="lifetime", nullable=true, default=7)
	 * @Rest\RequestParam(name="lat", nullable=true)
	 * @Rest\RequestParam(name="lon", nullable=true)
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

		$lat = $paramFetcher->get(self::LAT);
		$lon = $paramFetcher->get(self::LON);
		if (!$this->isValidNumber($lat, -90.0, 90.0) || !$this->isValidNumber($lon, 0.0, 180.0)) {
			// find user's location
			$loc = $this->session->getLocation();
			$lat = $loc[self::LAT];
			$lon = $loc[self::LON];
			if ($lat === 0 && $lon === 0) {
				throw new HttpException(400, 'The user profile has no address.');
			}
		}

		//add basket
		$basket = $this->service->addBasket($description, '', $paramFetcher->get(self::CONTACT_TYPES),
				$paramFetcher->get(self::TEL), $paramFetcher->get(self::MOBILE_NUMBER),
				$paramFetcher->get('weight'), $lat, $lon,
				$paramFetcher->get('lifetime'));
		if (!$basket) {
			throw new HttpException(400, 'Unable to create the basket.');
		}

		// return the created basket
		$data = $this->normalizeBasket($basket);

		return $this->handleView($this->view(['basket' => $data], 200));
	}

	/**
	 * Removes a basket of this user with the given ID. Returns 200 if a basket
	 * of the user was found and deleted, 404 if no such basket was found, or
	 * 401 if not logged in.
	 *
	 * @Rest\Delete("baskets/{basketId}", requirements={"basketId" = "\d+"})
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
