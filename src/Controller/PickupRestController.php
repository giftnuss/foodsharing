<?php

namespace Foodsharing\Controller;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\StoreTransactions;
use Foodsharing\Permissions\StorePermissions;
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
	private $storeTransactions;

	public function __construct(
		FoodsaverGateway $foodsaverGateway,
		Session $session,
		StoreGateway $storeGateway,
		StorePermissions $storePermissions,
		StoreTransactions $storeTransactions
	) {
		$this->foodsaverGateway = $foodsaverGateway;
		$this->session = $session;
		$this->storeGateway = $storeGateway;
		$this->storePermissions = $storePermissions;
		$this->storeTransactions = $storeTransactions;
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
	 * @Rest\Post("stores/{storeId}/pickups/{pickupDate}/{fsId}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+", "fsId" = "\d+"})
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

		$isConfirmed = $this->storeTransactions->joinPickup($storeId, $date, $fsId, $this->session->id());

		return $this->handleView($this->view([
			'isConfirmed' => $isConfirmed
		], 200));
	}

	/**
	 * @Rest\Delete("stores/{storeId}/pickups/{pickupDate}/{fsId}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+", "fsId" = "\d+"})
	 */
	public function leavePickupAction(int $storeId, string $pickupDate, int $fsId)
	{
		if (!$this->storePermissions->mayRemovePickupUser($storeId, $fsId)) {
			throw new HttpException(403);
		}

		$date = $this->parsePickupDate($pickupDate);
		if ($date < Carbon::now()) {
			throw new HttpException(400, 'Cannot modify pickup in the past.');
		}

		if (!$this->storeGateway->removeFetcher($fsId, $storeId, $date)) {
			throw new HttpException(400, 'Failed to remove user from pickup');
		}

		return $this->handleView($this->view([], 200));
	}

	/**
	 * @Rest\Patch("stores/{storeId}/pickups/{pickupDate}/{fsId}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+", "fsId" = "\d+"})
	 * @Rest\RequestParam(name="isConfirmed", nullable=true, default=null)
	 */
	public function editPickupSlotAction(int $storeId, string $pickupDate, int $fsId, ParamFetcher $paramFetcher)
	{
		if (!$this->storePermissions->mayConfirmPickup($storeId)) {
			throw new HttpException(403);
		}

		$date = $this->parsePickupDate($pickupDate);

		if ($paramFetcher->get('isConfirmed')) {
			if (!$this->storeGateway->confirmFetcher($fsId, $storeId, $date)) {
				throw new HttpException(400);
			}
		}

		return $this->handleView($this->view([], 200));
	}

	/**
	 * @Rest\Patch("stores/{storeId}/pickups/{pickupDate}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+"})
	 * @Rest\RequestParam(name="totalSlots", nullable=true, default=null)
	 */
	public function editPickupAction(int $storeId, string $pickupDate, ParamFetcher $paramFetcher)
	{
		if (!$this->storePermissions->mayEditPickups($storeId)) {
			throw new HttpException(403);
		}

		$date = $this->parsePickupDate($pickupDate);

		if ($date < Carbon::now()) {
			throw new HttpException(400, 'Cannot modify pickup in the past.');
		}

		$totalSlots = $paramFetcher->get('totalSlots');
		if (!is_null($totalSlots)) {
			if (!$this->storeTransactions->changePickupSlots($storeId, $date, $totalSlots)) {
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
		if (CarbonInterval::hours(Carbon::today()->diffInHours(Carbon::now()))->greaterThanOrEqualTo(CarbonInterval::hours(6))) {
			$fromTime = Carbon::today();
		} else {
			$fromTime = Carbon::today()->subHours(6);
		}

		$pickups = $this->storeGateway->getPickupSlots($storeId, $fromTime);

		return $this->handleView($this->view([
			'pickups' => $this->enrichPickupSlots($pickups, $storeId)
		]));
	}

	/**
	 * @Rest\Get("stores/{storeId}/history/{fromTS}/{toTS}", requirements={"storeId" = "\d+", "fromTS" = "\d+", "toTS" = "\d+"})
	 */
	public function listPickupHistoryAction(int $storeId, int $fromTS, int $toTS)
	{
		if (!$this->storePermissions->maySeePickupHistory($storeId)) {
			throw new HttpException(403);
		}
		// convert unix timestamps
		$fromDate = null;
		$toDate = null;
		try {
			$fromDate = Carbon::now()->min(Carbon::createFromTimestamp($fromTS));
			$toDate = Carbon::now()->min(Carbon::createFromTimestamp($toTS));
		} catch (\Exception $e) {
		}
		if (!$fromDate || !$toDate) {
			throw new HttpException(400, 'Invalid date format');
		}

		$pickups = [[
			'occupiedSlots' => $this->storeGateway->getFetchHistory($storeId, $fromDate, $toDate)
		]];

		return $this->handleView($this->view([
			'pickups' => $this->enrichPickupSlots($pickups, $storeId)
		]));
	}

	private function enrichPickupSlots(array $pickups, int $storeId): array
	{
		$profiles = [];
		foreach ($this->storeGateway->getStoreTeam($storeId) as $user) {
			$profiles[$user['id']] = RestNormalization::normalizeStoreUser($user);
		}
		foreach ($pickups as &$pickup) {
			foreach ($pickup['occupiedSlots'] as &$slot) {
				if (isset($profiles[$slot['foodsaverId']])) {
					$slot['profile'] = $profiles[$slot['foodsaverId']];
				} else {
					$details = $this->foodsaverGateway->getFoodsaverDetails($slot['foodsaverId']);
					$slot['profile'] = RestNormalization::normalizeStoreUser($details);
				}
				unset($slot['foodsaverId']);
			}
		}
		unset($pickup);
		usort($pickups, function ($a, $b) {
			return $a['date']->lt($b['date']) ? -1 : 1;
		});

		return $pickups;
	}
}
