<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Basket\BasketGateway;
use Foodsharing\Modules\Core\DBConstants\Basket\Status as BasketStatus;
use Foodsharing\Modules\Core\DBConstants\BasketRequests\Status as RequestStatus;
use Foodsharing\Services\BasketService;
use Foodsharing\Services\ImageService;
use Foodsharing\Services\MessageService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Rest controller for food baskets.
 */
final class BasketRestController extends AbstractFOSRestController
{
	private $gateway;
	private $service;
	private $imageService;
	private $messageService;
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

	private const BYTES_PER_KILOBYTE = 1024;
	private const KILOBYTES_PER_MEGABYTE = 1024;
	private const MAX_PICTURE_SIZE_BYTES = 60 * self::KILOBYTES_PER_MEGABYTE * self::BYTES_PER_KILOBYTE;
	private const SIZES = [800 => '', 450 => 'medium-', 200 => 'thumb-', 75 => '75x75-', 50 => '50x50-'];
	private const MAX_BASKET_DISTANCE = 50;

	public function __construct(
		BasketGateway $gateway,
		BasketService $service,
		ImageService $imageService,
		MessageService $messageService,
		Session $session
	) {
		$this->gateway = $gateway;
		$this->service = $service;
		$this->imageService = $imageService;
		$this->messageService = $messageService;
		$this->session = $session;
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
	 */
	public function listBasketsAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$baskets = [];
		switch ($paramFetcher->get('type')) {
			case 'mine':
				$baskets = $this->getCurrentUsersBaskets();
				break;
			case 'coordinates':
				$baskets = $this->gateway->getBasketCoordinates();
				break;
		}

		return $this->handleView($this->view(['baskets' => $baskets], 200));
	}

	/**
	 * Returns a list of baskets close to a given location. If the location is not valid the user's
	 * home location is used. The distance is measured in kilometers.
	 * Does not include baskets created by the current user.
	 *
	 * Returns 200 and a list of baskets, 400 if the distance is out of range, or 401 if not logged in.
	 *
	 * @Rest\Get("baskets/nearby")
	 * @Rest\QueryParam(name="lat", nullable=true)
	 * @Rest\QueryParam(name="lon", nullable=true)
	 * @Rest\QueryParam(name="distance", nullable=false, requirements="\d+")
	 */
	public function listNearbyBasketsAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$location = $this->fetchLocationOrUserHome($paramFetcher);
		$distance = $paramFetcher->get('distance');
		if ($distance < 1 || $distance > self::MAX_BASKET_DISTANCE) {
			throw new HttpException(400, 'distance must be positive and <= ' . self::MAX_BASKET_DISTANCE);
		}

		$baskets = $this->gateway->listNearbyBasketsByDistance($this->session->id(), $location, $distance);
		$baskets = array_map(function ($b) {
			$basket = $this->gateway->getBasket((int)$b[self::ID]);
			$request = $this->gateway->getRequest($basket[self::ID], $this->session->id(), $basket['foodsaver_id']);
			if ($request) {
				$request = [$request];
			}

			return $this->normalizeBasket($basket, $request);
		}, $baskets);

		return $this->handleView($this->view(['baskets' => $baskets], 200));
	}

	private function getCurrentUsersBaskets()
	{
		$updates = $this->gateway->listUpdates($this->session->id());
		$baskets = $this->gateway->listMyBaskets($this->session->id());
		$baskets = array_map(function ($b) use ($updates) {
			return $this->normalizeMyBasket($b, $updates);
		}, $baskets);

		return $baskets;
	}

	/**
	 * Normalizes the details of a basket of the current user for the Rest
	 * response, including requests.
	 *
	 * @param array $basketData basket data
	 * @param array $updates list of updates
	 */
	private function normalizeMyBasket(array $basketData, array $updates = []): array
	{
		$basket = [
			self::ID => (int)$basketData[self::ID],
			self::DESCRIPTION => html_entity_decode($basketData[self::DESCRIPTION]),
			self::PICTURE => $basketData[self::PICTURE],
			self::CREATED_AT => (int)$basketData[self::TIME_TS],
			self::UPDATED_AT => (int)$basketData[self::TIME_TS],
			self::REQUESTS => []
		];

		// add requests, if there are any in the updates
		foreach ($updates as $update) {
			if ((int)$update[self::ID] === $basket[self::ID]) {
				$basket[self::REQUESTS][] = $this->normalizeRequest($update);
				$basket[self::UPDATED_AT] = max($basket[self::UPDATED_AT], (int)$update[self::TIME_TS]);
			}
		}

		return $basket;
	}

	/**
	 * Normalizes a basket request.
	 */
	private function normalizeRequest(array $request): array
	{
		$user = RestNormalization::normalizeUser($request, 'fs_');

		return [
			'user' => $user,
			'time' => $request[self::TIME_TS],
		];
	}

	/**
	 * Returns details of the basket with the given ID. Returns 200 and the
	 * basket, 500 if the basket does not exist, or 401 if not logged in.
	 *
	 * @Rest\Get("baskets/{basketId}", requirements={"basketId" = "\d+"})
	 */
	public function getBasketAction(int $basketId): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$basket = $this->gateway->getBasket($basketId);
		$this->verifyBasketIsAvailable($basket);
		if ($basket['fs_id'] == $this->session->id()) {
			$requests = $this->gateway->listRequests($basketId, $this->session->id());
		} else {
			$requests = $this->gateway->getRequest($basketId, $this->session->id(), $basket['foodsaver_id']);
			if ($requests) {
				$requests = [$requests];
			}
		}
		$basket = $this->normalizeBasket($basket, $requests);

		return $this->handleView($this->view(['basket' => $basket], 200));
	}

	/**
	 * Normalizes the details of a basket for the Rest response.
	 *
	 * @param array $basketData the basket data
	 */
	private function normalizeBasket(array $basketData, array $updates = []): array
	{
		// set main properties
		$creator = RestNormalization::normalizeUser($basketData, 'fs_');
		$basket = [
			self::ID => (int)$basketData[self::ID],
			self::STATUS => (int)$basketData[self::STATUS],
			self::DESCRIPTION => html_entity_decode($basketData[self::DESCRIPTION]),
			self::PICTURE => $basketData[self::PICTURE],
			self::CONTACT_TYPES => array_map('\intval', explode(':', $basketData['contact_type'])),
			self::CREATED_AT => (int)$basketData[self::TIME_TS],
			self::UPDATED_AT => (int)$basketData[self::TIME_TS],
			'until' => (int)$basketData['until_ts'],
			self::LAT => (float)$basketData[self::LAT],
			self::LON => (float)$basketData[self::LON],
			'creator' => $creator,
			'requestCount' => $basketData['request_count'],
			self::REQUESTS => []
		];

		// add phone numbers if contact_type includes telephone
		$tel = '';
		$handy = '';
		$telephoneContactType = 2;
		if (isset($basketData['contact_type']) && \in_array($telephoneContactType, $basket[self::CONTACT_TYPES], true)) {
			$tel = $basketData[self::TEL];
			$handy = $basketData[self::MOBILE_NUMBER];
		}
		$basket[self::TEL] = $tel;
		$basket[self::MOBILE_NUMBER] = $handy;

		// add requests, if there are any in the updates
		foreach ($updates as $update) {
			if ((int)$update[self::ID] === $basket[self::ID]) {
				$basket[self::REQUESTS][] = $this->normalizeRequest($update);
				$basket[self::UPDATED_AT] = max($basket[self::UPDATED_AT], (int)$update[self::TIME_TS]);
			}
		}

		return $basket;
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
	 */
	public function addBasketAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		// prepare and check description
		$description = trim(strip_tags($paramFetcher->get(self::DESCRIPTION)));
		if (empty($description)) {
			throw new HttpException(400, 'The description must not be empty.');
		}

		$location = $this->fetchLocationOrUserHome($paramFetcher);

		$contactTypes = $paramFetcher->get(self::CONTACT_TYPES);
		if ($contactTypes !== null && \is_array($contactTypes)) {
			$contactTypes = array_map('intval', $contactTypes);
		}

		$basket = $this->service->addBasket(
			$description,
			'',
			$contactTypes,
			$paramFetcher->get(self::TEL),
			$paramFetcher->get(self::MOBILE_NUMBER),
			$paramFetcher->get('weight'),
			$location['lat'],
			$location['lon'],
			$paramFetcher->get('lifetime')
		);

		if (!$basket) {
			throw new HttpException(400, 'Unable to create the basket.');
		}

		return $this->getBasketAction($basket[self::ID]);
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
	 * Removes a basket of this user with the given ID. Returns 200 if a basket
	 * of the user was found and deleted, 404 if no such basket was found, or
	 * 401 if not logged in.
	 *
	 * @Rest\Delete("baskets/{basketId}", requirements={"basketId" = "\d+"})
	 */
	public function removeBasketAction(int $basketId): ?Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$status = $this->gateway->removeBasket($basketId, $this->session->id());

		if ($status === 0) {
			throw new HttpException(404, 'Basket was not found or cannot be deleted.');
		}

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Updates the description of an existing basket. The description must not be empty. If the location
	 * is not given or invalid it falls back to the user's home. Returns the updated basket.
	 *
	 * @Rest\Put("baskets/{basketId}", requirements={"basketId" = "\d+"})
	 * @Rest\RequestParam(name="description", nullable=false)
	 * @Rest\RequestParam(name="lat", nullable=true)
	 * @Rest\RequestParam(name="lon", nullable=true)
	 *
	 * @param int $basketId ID of an existing basket
	 */
	public function editBasketAction(int $basketId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$basket = $this->findEditableBasket($basketId);

		// prepare and check description
		$description = trim(strip_tags($paramFetcher->get(self::DESCRIPTION)));
		if (empty($description)) {
			throw new HttpException(400, 'The description must not be empty.');
		}

		$location = $this->fetchLocationOrUserHome($paramFetcher, [self::LAT => $basket[self::LAT], self::LON => $basket[self::LON]]);

		//update basket
		$this->gateway->editBasket($basketId, $description, $basket[self::PICTURE], $location[self::LAT],
			$location[self::LON], $this->session->id());

		return $this->getBasketAction($basketId);
	}

	/**
	 * Sets a new picture for this basket.
	 *
	 * @Rest\Put("baskets/{basketId}/picture", requirements={"basketId" = "\d+"})
	 *
	 * @param int $basketId ID of an existing basket
	 */
	public function setPictureAction(int $basketId, Request $request): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$basket = $this->findEditableBasket($basketId);

		$data = $request->getContent();
		if ($data === '') {
			throw new HttpException(400, 'The picture data must not be empty.');
		}
		if (strlen($data) > self::MAX_PICTURE_SIZE_BYTES) {
			$maxPictureSizeMegabytes = self::MAX_PICTURE_SIZE_BYTES / (self::KILOBYTES_PER_MEGABYTE * self::BYTES_PER_KILOBYTE);
			throw new HttpException(400, 'The picture data must not exceed ' . $maxPictureSizeMegabytes . ' MB.');
		}

		//save and resize image
		$tmp = uniqid('tmp/', true);
		file_put_contents($tmp, $request->getContent());
		try {
			$picname = $this->imageService->createResizedPictures($tmp, 'images/basket/', self::SIZES);
			unlink($tmp);
		} catch (\Exception $e) {
			throw new HttpException(400, 'Picture could not be resized: ' . $e->getMessage());
		}

		//remove old images
		if (isset($basket[self::PICTURE]) && $basket[self::PICTURE] !== '') {
			$this->imageService->removeResizedPictures('images/basket/', $basket[self::PICTURE], self::SIZES);
		}

		//update basket
		$basket[self::PICTURE] = $picname;
		$this->gateway->editBasket($basketId, $basket[self::DESCRIPTION], $picname, $basket[self::LAT], $basket[self::LON], $this->session->id());

		return $this->getBasketAction($basketId);
	}

	/**
	 * Sets a new picture for this basket.
	 *
	 * @Rest\Delete("baskets/{basketId}/picture", requirements={"basketId" = "\d+"})
	 *
	 * @param int $basketId ID of an existing basket
	 */
	public function removePictureAction(int $basketId): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		//update basket
		$basket = $this->findEditableBasket($basketId);
		if (isset($basket[self::PICTURE])) {
			$this->imageService->removeResizedPictures('images/basket/', $basket[self::PICTURE], self::SIZES);
			$basket[self::PICTURE] = null;
			$this->gateway->editBasket($basketId, $basket[self::DESCRIPTION], null, $basket[self::LAT], $basket[self::LON], $this->session->id());
		}

		return $this->getBasketAction($basketId);
	}

	/**
	 * Requests a basket.
	 *
	 * @Rest\Post("baskets/{basketId}/request", requirements={"basketId" = "\d+"})
	 * @Rest\RequestParam(name="message", nullable=false)
	 *
	 * @param int $basketId ID of an existing basket
	 */
	public function requestBasketAction(int $basketId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$message = trim(strip_tags($paramFetcher->get('message')));

		if (empty($message)) {
			throw new HttpException(400, 'The request message should not be empty.');
		}

		$basket = $this->gateway->getBasket($basketId);
		$this->verifyBasketIsAvailable($basket);

		$basketCreatorId = $basket['foodsaver_id'];

		// check for existing request
		$requestStatus = $this->gateway->getRequestStatus($basketId, $this->session->id(), $basketCreatorId);
		if ($requestStatus && $requestStatus[self::STATUS] === RequestStatus::DENIED) {
			throw new HttpException(403, 'Your request was denied by the basket creator.');
		}

		// Send the message to the creator
		$this->messageService->sendMessageToUser($basketCreatorId, $this->session->id(), $message, 'basket/request');
		$this->gateway->setStatus($basketId, RequestStatus::REQUESTED_MESSAGE_UNREAD, $this->session->id());

		return $this->getBasketAction($basketId);
	}

	/**
	 * Withdraw a basket request.
	 *
	 * @Rest\Post("baskets/{basketId}/withdraw", requirements={"basketId" = "\d+"})
	 *
	 * @param int $basketId ID of an existing basket
	 */
	public function withdrawBasketRequestAction(int $basketId): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$basket = $this->gateway->getBasket($basketId);
		$this->verifyBasketIsAvailable($basket);

		$basketCreatorId = $basket['foodsaver_id'];

		// Check that there is an existing active request. If not, there is nothing to withdraw and nothing to be done.
		$requestStatus = $this->gateway->getRequestStatus($basketId, $this->session->id(), $basketCreatorId);
		if ($requestStatus && ($requestStatus[self::STATUS] === RequestStatus::REQUESTED_MESSAGE_UNREAD || $requestStatus[self::STATUS] === RequestStatus::REQUESTED_MESSAGE_READ)) {
			$this->gateway->setStatus($basketId, RequestStatus::DELETED_OTHER_REASON, $this->session->id());
		}

		return $this->getBasketAction($basketId);
	}

	/**
	 * Finds and returns the user's basket with the given id. Throws HttpExceptions
	 * if the basket does not exist, was deleted, or is owned by a different user.
	 *
	 * @param int $basketId id of a basket
	 *
	 * @return array the basket's entry from the database
	 */
	private function findEditableBasket(int $basketId): array
	{
		$basket = $this->gateway->getBasket($basketId);

		$this->verifyBasketIsAvailable($basket);
		if ($basket['fs_id'] !== $this->session->id()) {
			throw new HttpException(401, 'You are not the owner of the basket.');
		}

		return $basket;
	}

	/**
	 * Verifies that the basket was not deleted and is not expired. Otherwise this
	 * method throws an appropriate HttpException.
	 */
	private function verifyBasketIsAvailable(array $basket): void
	{
		if (!$basket || $basket[self::STATUS] === BasketStatus::DELETED_OTHER_REASON) {
			throw new HttpException(404, 'Basket does not exist.');
		}

		if ($basket[self::STATUS] === BasketStatus::DELETED_PICKED_UP) {
			throw new HttpException(404, 'Basket was already picked up.');
		}

		if ($basket['until_ts'] < time()) {
			throw new HttpException(404, 'Basket is expired.');
		}
	}

	/**
	 * Returns a location from the param fetcher in the 'lat' and 'lon' fields. If none
	 * is given, it returns the default location or the user's home address, if the default
	 * location is null.
	 *
	 * @param array $defaultLocation a fallback value or null
	 *
	 * @return array the location
	 *
	 * @throws \HttpException if no location and no default location were given and the user's
	 * home address is not set
	 */
	private function fetchLocationOrUserHome(ParamFetcher $paramFetcher, array $defaultLocation = null): array
	{
		$lat = $paramFetcher->get(self::LAT);
		$lon = $paramFetcher->get(self::LON);
		if (!$this->isValidNumber($lat, -90.0, 90.0) || !$this->isValidNumber($lon, -180.0, 180.0)) {
			if ($defaultLocation !== null) {
				return $defaultLocation;
			} else {
				// find user's location
				$loc = $this->session->getLocation();
				$lat = $loc[self::LAT];
				$lon = $loc[self::LON];
				if ($lat === 0 && $lon === 0) {
					throw new HttpException(400, 'The user profile has no address.');
				}
			}
		}

		return ['lat' => $lat, 'lon' => $lon];
	}
}
