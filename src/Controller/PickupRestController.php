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
use FOS\RestBundle\Request\ParamFetcher;
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

	private function parsePickupDate(string $pickupDate): Carbon
	{
		$date = null;
		try {
			$date = @Carbon::createFromFormat(DATE_ATOM, $pickupDate);
		} catch (\Exception $e) {
		}
		if (!$date) {
			try {
				$date = Carbon::createFromFormat('Y-m-d\TH:i:s.uP', $pickupDate);
			} catch (\Exception $e) {
			}
			if (!$date) {
				throw new HttpException(400, 'Invalid date format');
			}
		}

		return $date;
	}

	/**
	 * @Rest\Post("stores/{storeId}/{pickupDate}/{fsId}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+", "fsId" = "\d+"})
	 */
	public function joinPickupAction(int $storeId, string $pickupDate, int $fsId)
	{
		if ($fsId != $this->session->id()) {
			/* currently it is forbidden to add other users to a pickup */
			throw new HttpException(403);
		}
		if (!$this->storePermissions->mayDoPickup($storeId)) {
			throw new HttpException(403);
		}

		$date = $this->parsePickupDate($pickupDate);
		$confirmed = $this->storePermissions->hasPreconfirmedPickup($storeId);
		if (!$this->storeService->joinPickup($fsId, $storeId, $date, $confirmed)) {
			throw new HttpException(400, 'No pickup slot available');
		}

		return $this->handleView($this->view([
			'confirmed' => $confirmed
		], 200));
	}

	/**
	 * @Rest\Delete("stores/{storeId}/{pickupDate}/{fsId}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+", "fsId" = "\d+"})
	 *
	 * @param int $storeId
	 * @param string $pickupDate
	 * @param int $fsId
	 */
	public function leavePickupAction(int $storeId, string $pickupDate, int $fsId)
	{
		if (!$this->storePermissions->mayRemovePickupUser($storeId, $fsId)) {
			throw new HttpException(403);
		}

		$date = $this->parsePickupDate($pickupDate);

		if (!$this->storeService->leavePickup($storeId, $date, $fsId)) {
			throw new HttpException(400, 'Failed to remove user from pickup');
		}

		return $this->handleView($this->view([], 200));
	}

	/**
	 * @Rest\Patch("stores/{storeId}/{pickupDate}/{fsId}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+", "fsId" = "\d+"})
	 * @Rest\RequestParam(name="isConfirmed", nullable=true, default=null)
	 */
	public function editPickupSlotAction(int $storeId, string $pickupDate, int $fsId, ParamFetcher $paramFetcher)
	{
		if (!$this->storePermissions->mayConfirmPickup($storeId)) {
			throw new HttpException(403);
		}

		$date = $this->parsePickupDate($pickupDate);

		if ($paramFetcher->get('isConfirmed')) {
			if (!$this->storeService->confirmPickup($storeId, $date, $fsId)) {
				throw new HttpException(400);
			}
		}

		return $this->handleView($this->view([], 200));
	}

	/**
	 * @Rest\Patch("stores/{storeId}/{pickupDate}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+"})
	 * @Rest\RequestParam(name="addSlot", nullable=true, default=null)
	 * @Rest\RequestParam(name="removeSlot", nullable=true, default=null)
	 */
	public function editPickupAction(int $storeId, string $pickupDate, ParamFetcher $paramFetcher)
	{
		if (!$this->storePermissions->mayEditPickups($storeId)) {
			throw new HttpException(403);
		}

		$date = $this->parsePickupDate($pickupDate);

		if ($paramFetcher->get('addSlot')) {
			if (!$this->storeService->changePickupSlots($storeId, $date, 1)) {
				throw new HttpException(400);
			}
		}
		if ($paramFetcher->get('removeSlot')) {
			if (!$this->storeService->changePickupSlots($storeId, $date, -1)) {
				throw new HttpException(400);
			}
		}

		return $this->handleView($this->view([], 200));
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
				if (isset($profiles[$slot['foodsaverId']])) {
					$slot['profile'] = $profiles[$slot['foodsaverId']];
				} else {
					$slot['profile'] = RestNormalization::normalizeFoodsaver($this->foodsaverGateway->getFoodsaverDetails($slot['foodsaverId']));
				}
				unset($slot['foodsaverId']);
			}
		}
		unset($pickup);
		usort($pickups, function ($a, $b) {
			return $a['date']->lt($b['date']) ? -1 : 1;
		});

		return $this->handleView($this->view([
			'data' => $pickups
		]));
	}
}
