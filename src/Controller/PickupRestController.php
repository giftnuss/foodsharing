<?php

namespace Foodsharing\Controller;

use Carbon\Carbon;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Services\StoreService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class PickupRestController extends AbstractFOSRestController
{
	private $foodsaverGateway;
	private $session;
	private $storeGateway;
	private $storePermissions;
	private $storeService;

	public function __construct(FoodsaverGateway $foodsaverGateway, Session $session, StoreGateway $storeGateway, StorePermissions $storePermissions, StoreService $storeService)
	{
		$this->foodsaverGateway = $foodsaverGateway;
		$this->session = $session;
		$this->storeGateway = $storeGateway;
		$this->storePermissions = $storePermissions;
		$this->storeService = $storeService;
	}

	/**
	 * @Rest\Post("stores/{storeId}/{pickupDate}/signup", requirements={"storeId" = "\d+"})
	 */
	public function signupForPickupAction(int $storeId, string $pickupDate)
	{
		if (!$this->storePermissions->mayDoPickup($storeId)) {
			throw new HttpException(403);
		}

		$date = Carbon::createFromFormat(DATE_ATOM, $pickupDate);
		if (!$date) {
			throw new HttpException(400, 'Invalid date format');
		}
		$confirmed = $this->storePermissions->hasPreconfirmedPickup($storeId);
		if (!$this->storeService->signupForPickup($this->session->id(), $storeId, $date, $confirmed)) {
			throw new HttpException(400, 'No pickup slot available');
		}

		return $this->handleView($this->view([
			'confirmed' => $confirmed
		], 200));
	}

	/**
	 * @Rest\Get("stores/{storeId}/pickups", requirements={"storeId" = "\d+"})
	 */
	public function listPickupsAction(int $storeId)
	{
		if (!$this->storePermissions->maySeePickups($storeId)) {
			throw new HttpException(403);
		}

		$pickups = $this->storeService->listPickupSlots($storeId);
		$profiles = [];
		foreach ($this->storeGateway->getBetriebTeam($storeId) as $user) {
			$profiles[$user['id']] = RestNormalization::normalizeFoodsaver($user);
		}
		foreach ($pickups as &$pickup) {
			foreach ($pickup['occupiedSlots'] as &$slot) {
				if (isset($profiles[$slot['foodsaver_id']])) {
					$slot['profile'] = $profiles[$slot['foodsaver_id']];
				} else {
					$slot['profile'] = RestNormalization::normalizeFoodsaver($this->foodsaverGateway->getFoodsaverDetails($slot['foodsaver_id']));
				}
			}
		}
		unset($pickup);

		return $this->handleView($this->view([
			'data' => $pickups
		]));
	}
}
