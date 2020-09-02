<?php

namespace Foodsharing\Controller;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
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

		$this->storeGateway->addStoreLog($storeId, $fsId, null, $date, StoreLogAction::SIGN_UP_SLOT);
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

		if ($this->session->id() === $fsId) {
			$this->storeGateway->addStoreLog( // the user removed their own pickup
				$storeId,
				$fsId,
				null,
				$date,
				StoreLogAction::SIGN_OUT_SLOT
			);
		} else {
			$this->storeGateway->addStoreLog( // the user got kicked/the pickup got denied
				$storeId,
				$this->session->id(),
				$fsId,
				$date,
				StoreLogAction::REMOVED_FROM_SLOT
			);
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
			$this->storeGateway->addStoreLog(
				$storeId,
				$this->session->id(),
				$fsId,
				$date,
				StoreLogAction::SLOT_CONFIRMED
			);

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
	 * @Rest\Get("stores/{storeId}/history/{fromDate}/{toDate}", requirements={"storeId" = "\d+", "fromDate" = "[^/]+", "toDate" = "[^/]+"})
	 */
	public function listPickupHistoryAction(int $storeId, string $fromDate, string $toDate)
	{
		if (!$this->storePermissions->maySeePickupHistory($storeId)) {
			throw new HttpException(403);
		}
		// convert date strings into datetime objects
		$from = null;
		$to = null;
		try {
			$from = $this->parsePickupDate($fromDate)->min(Carbon::now());
			$to = $this->parsePickupDate($toDate)->min(Carbon::now());
		} catch (\Exception $e) {
		}
		if (!$from || !$to) {
			throw new HttpException(400, 'Invalid date format');
		}

		$pickups = [[
			'occupiedSlots' => $this->storeGateway->getPickupHistory($storeId, $from, $to)
		]];

		return $this->handleView($this->view([
			'pickups' => $this->enrichPickupSlots($pickups, $storeId)
		]));
	}

	private function enrichPickupSlots(array $pickups, int $storeId): array
	{
		$team = [];
		foreach ($this->storeGateway->getStoreTeam($storeId) as $user) {
			$team[$user['id']] = RestNormalization::normalizeStoreUser($user);
		}
		foreach ($pickups as &$pickup) {
			foreach ($pickup['occupiedSlots'] as &$slot) {
				if (isset($team[$slot['foodsaverId']])) {
					$slot['profile'] = $team[$slot['foodsaverId']];
				} else {
					$details = $this->foodsaverGateway->getFoodsaver($slot['foodsaverId']);
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
