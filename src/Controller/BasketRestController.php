<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Basket\BasketGateway;
use Foodsharing\Modules\Core\DBConstants\BasketRequests\Status;
use Foodsharing\Services\BasketService;
use Foodsharing\Services\ImageService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Rest controller for food baskets.
 */
final class BasketRestController extends AbstractFOSRestController
{
	private $gateway;
	private $service;
	private $imageService;
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

	public function __construct(BasketGateway $gateway, BasketService $service, ImageService $imageService, Session $session)
	{
		$this->gateway = $gateway;
		$this->service = $service;
		$this->imageService = $imageService;
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
	 *
	 * @param ParamFetcher $paramFetcher
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listBasketsAction(ParamFetcher $paramFetcher): \Symfony\Component\HttpFoundation\Response
	{
		$this->throwExceptionIfNotLoggedIn();

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

	private function throwExceptionIfNotLoggedIn()
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}
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
	 *
	 * @return array
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
			if ((int)$update[self::ID] == $basket[self::ID]) {
				$basket[self::REQUESTS][] = $this->normalizeRequest($update);
				$basket[self::UPDATED_AT] = max($basket[self::UPDATED_AT], (int)$update[self::TIME_TS]);
			}
		}

		return $basket;
	}

	/**
	 * Normalizes a basket request.
	 *
	 * @param array $request
	 *
	 * @return array
	 */
	private function normalizeRequest(array $request): array
	{
		$user = RestNormalization::normalizeFoodsaver($request, 'fs_');

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
	 *
	 * @param int $basketId
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getBasketAction(int $basketId): \Symfony\Component\HttpFoundation\Response
	{
		$this->throwExceptionIfNotLoggedIn();

		$basket = $this->gateway->getBasket($basketId);
		if (!$basket || $basket[self::STATUS] == Status::DELETED_OTHER_REASON) {
			throw new HttpException(404, 'Basket does not exist.');
		} elseif ($basket[self::STATUS] == Status::DELETED_PICKED_UP) {
			throw new HttpException(404, 'Basket was already picked up.');
		}

		$basket = $this->normalizeBasket($basket);

		return $this->handleView($this->view(['basket' => $basket], 200));
	}

	/**
	 * Normalizes the details of a basket for the Rest response.
	 *
	 * @param array $basketData the basket data
	 *
	 * @return array
	 */
	private function normalizeBasket(array $basketData): array
	{
		// set main properties
		$creator = RestNormalization::normalizeFoodsaver($basketData, 'fs_');
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
			'creator' => $creator
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
	 *
	 * @param ParamFetcher $paramFetcher
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function addBasketAction(ParamFetcher $paramFetcher): \Symfony\Component\HttpFoundation\Response
	{
		$this->throwExceptionIfNotLoggedIn();

		$description = $this->getValidatedDescriptionOrThrowException($paramFetcher);

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

		$basket = $this->createAndReturnBasketOrThrowException($paramFetcher, $description, $lat, $lon);

		// return the created basket
		$basket = $this->normalizeBasket($basket);

		return $this->handleView($this->view(['basket' => $basket], 200));
	}

	/**
	 * Checks if the number is a valid value in the given range.
	 */
	private function isValidNumber($value, $lowerBound, $upperBound): bool
	{
		return !is_null($value) && !is_nan($value)
			&& ($lowerBound <= $value) && ($upperBound >= $value);
	}

	private function getValidatedDescriptionOrThrowException(ParamFetcher $paramFetcher)
	{
		// prepare and check description
		$description = trim(strip_tags($paramFetcher->get(self::DESCRIPTION)));
		if (empty($description)) {
			throw new HttpException(400, 'The description must not be empty.');
		}
	}

	private function createAndReturnBasketOrThrowException(ParamFetcher $paramFetcher, $description, $lat, $lon)
	{
		$basket = $this->service->addBasket(
			$description,
			'',
			$paramFetcher->get(self::CONTACT_TYPES),
			$paramFetcher->get(self::TEL),
			$paramFetcher->get(self::MOBILE_NUMBER),
			$paramFetcher->get('weight'),
			$lat,
			$lon,
			$paramFetcher->get('lifetime')
		);

		if (!$basket) {
			throw new HttpException(400, 'Unable to create the basket.');
		}

		return $basket;
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
	 * @return \Symfony\Component\HttpFoundation\Response|null
	 */
	public function removeBasketAction(int $basketId): ?\Symfony\Component\HttpFoundation\Response
	{
		$this->throwExceptionIfNotLoggedIn();
		$this->removeBasketOrThrowException($basketId);

		return $this->handleView($this->view([], 200));
	}

	private function removeBasketOrThrowException(int $basketId)
	{
		$status = $this->gateway->removeBasket($basketId, $this->session->id());
		if ($status === 0) {
			throw new HttpException(404, 'Basket was not found or cannot be deleted.');
		}
	}

	/**
	 * Sets a new picture for this basket.
	 *
	 * @Rest\Put("baskets/{basketId}/picture", requirements={"basketId" = "\d+"})
	 *
	 * @param int $basketId ID of an existing basket
	 * @param Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function setPictureAction(int $basketId, Request $request): \Symfony\Component\HttpFoundation\Response
	{
		$this->throwExceptionIfNotLoggedIn();

		$basket = $this->findEditableBasket($basketId);

		$data = $this->getValidatedDataOrThrowException($request);

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
		$this->gateway->editBasket($basketId, $basket[self::DESCRIPTION], $picname, $this->session->id());

		$data = $this->normalizeBasket($basket);

		return $this->handleView($this->view(['basket' => $data], 200));
	}

	private function getValidatedDataOrThrowException(Request $request)
	{
		$data = $request->getContent();
		if ($data === '') {
			throw new HttpException(400, 'The picture data must not be empty.');
		}
		if (strlen($data) > self::MAX_PICTURE_SIZE_BYTES) {
			$maxPictureSizeMegabytes = self::MAX_PICTURE_SIZE_BYTES / (self::KILOBYTES_PER_MEGABYTE * self::BYTES_PER_KILOBYTE);
			throw new HttpException(400, 'The picture data must not exceed ' . $maxPictureSizeMegabytes . ' MB.');
		}
	}

	/**
	 * Sets a new picture for this basket.
	 *
	 * @Rest\Delete("baskets/{basketId}/picture", requirements={"basketId" = "\d+"})
	 *
	 * @param int $basketId ID of an existing basket
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function removePictureAction(int $basketId): \Symfony\Component\HttpFoundation\Response
	{
		$this->throwExceptionIfNotLoggedIn();

		//update basket
		$basket = $this->removePictureFromBasketAndReturnBasket($basketId);

		$basket = $this->normalizeBasket($basket);

		return $this->handleView($this->view(['basket' => $basket], 200));
	}

	private function removePictureFromBasketAndReturnBasket(int $basketId)
	{
		$basket = $this->findEditableBasket($basketId);
		if (isset($basket[self::PICTURE])) {
			$this->imageService->removeResizedPictures('images/basket/', $basket[self::PICTURE], self::SIZES);
			$basket[self::PICTURE] = null;
			$this->gateway->editBasket($basketId, $basket[self::DESCRIPTION], null, $this->session->id());
		}

		return $basket;
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
		$this->validateBasketIsEditableOrThrowException($basket);

		return $basket;
	}

	private function validateBasketIsEditableOrThrowException($basket)
	{
		$this->throwExceptionIfBasketDoesNotExist($basket);
		$this->throwExceptionIfBasketIsOwnedByOtherUser($basket);
	}

	private function throwExceptionIfBasketDoesNotExist($basket)
	{
		if (!$basket || $basket[self::STATUS] === Status::DELETED_OTHER_REASON
			|| $basket[self::STATUS] === Status::DELETED_PICKED_UP) {
			throw new HttpException(404, 'Basket does not exist or was deleted.');
		}
	}

	private function throwExceptionIfBasketIsOwnedByOtherUser($basket)
	{
		if ($basket['fs_id'] !== $this->session->id()) {
			throw new HttpException(401, 'You are not the owner of the basket.');
		}
	}
}
