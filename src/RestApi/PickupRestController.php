<?php

namespace Foodsharing\RestApi;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Message\MessageTransactions;
use Foodsharing\Modules\Store\PickupGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\StoreTransactions;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Utility\TimeHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class PickupRestController extends AbstractFOSRestController
{
	private FoodsaverGateway $foodsaverGateway;
	private Session $session;
	private PickupGateway $pickupGateway;
	private StoreGateway $storeGateway;
	private StorePermissions $storePermissions;
	private StoreTransactions $storeTransactions;
	private MessageTransactions $messageTransactions;

	public function __construct(
		FoodsaverGateway $foodsaverGateway,
		Session $session,
		PickupGateway $pickupGateway,
		StoreGateway $storeGateway,
		StorePermissions $storePermissions,
		StoreTransactions $storeTransactions,
		MessageTransactions $messageTransactions
	) {
		$this->foodsaverGateway = $foodsaverGateway;
		$this->session = $session;
		$this->pickupGateway = $pickupGateway;
		$this->storeGateway = $storeGateway;
		$this->storePermissions = $storePermissions;
		$this->storeTransactions = $storeTransactions;
		$this->messageTransactions = $messageTransactions;
	}

	/**
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Post("stores/{storeId}/pickups/{pickupDate}/{fsId}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+", "fsId" = "\d+"})
	 */
	public function joinPickupAction(int $storeId, string $pickupDate, int $fsId): Response
	{
		if ($fsId != $this->session->id()) {
			/* currently it is forbidden to add other users to a pickup */
			throw new HttpException(403);
		}
		if (!$this->storePermissions->mayDoPickup($storeId)) {
			throw new HttpException(403);
		}

		$date = TimeHelper::parsePickupDate($pickupDate);
		if (is_null($date)) {
			throw new HttpException(400, 'Invalid date format');
		}

		$isConfirmed = $this->storeTransactions->joinPickup($storeId, $date, $fsId, $this->session->id());

		$this->storeGateway->addStoreLog($storeId, $fsId, null, $date, StoreLogAction::SIGN_UP_SLOT);

		return $this->handleView($this->view([
			'isConfirmed' => $isConfirmed
		], 200));
	}

	/**
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Delete("stores/{storeId}/pickups/{pickupDate}/{fsId}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+", "fsId" = "\d+"})
	 * @RequestParam(name="message", nullable=true, default="")
	 */
	public function leavePickupAction(int $storeId, string $pickupDate, int $fsId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->storePermissions->mayRemovePickupUser($storeId, $fsId)) {
			throw new HttpException(403);
		}

		$date = TimeHelper::parsePickupDate($pickupDate);
		if (is_null($date)) {
			throw new HttpException(400, 'Invalid date format');
		}

		if ($date < Carbon::now()) {
			throw new HttpException(400, 'Cannot modify pickup in the past.');
		}

		if (!$this->pickupGateway->removeFetcher($fsId, $storeId, $date)) {
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
			$message = trim($paramFetcher->get('message'));
			$this->storeGateway->addStoreLog( // the user got kicked/the pickup got denied
				$storeId,
				$this->session->id(),
				$fsId,
				$date,
				StoreLogAction::REMOVED_FROM_SLOT,
				null,
				empty($message) ? null : $message
			);

			// send direct message to the user
			$formattedMessage = $this->storeTransactions->createKickMessage($fsId, $storeId, $date, $message);
			$this->messageTransactions->sendMessageToUser($fsId, $this->session->id(), $formattedMessage);
		}

		return $this->handleView($this->view([], 200));
	}

	/**
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Patch("stores/{storeId}/pickups/{pickupDate}/{fsId}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+", "fsId" = "\d+"})
	 * @Rest\RequestParam(name="isConfirmed", nullable=true, default=null)
	 */
	public function editPickupSlotAction(int $storeId, string $pickupDate, int $fsId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->storePermissions->mayConfirmPickup($storeId)) {
			throw new HttpException(403);
		}

		$date = TimeHelper::parsePickupDate($pickupDate);
		if (is_null($date)) {
			throw new HttpException(400, 'Invalid date format');
		}

		if ($paramFetcher->get('isConfirmed')) {
			if (!$this->pickupGateway->confirmFetcher($fsId, $storeId, $date)) {
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
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Patch("stores/{storeId}/pickups/{pickupDate}", requirements={"storeId" = "\d+", "pickupDate" = "[^/]+"})
	 * @Rest\RequestParam(name="totalSlots", nullable=true, default=null)
	 */
	public function editPickupAction(int $storeId, string $pickupDate, ParamFetcher $paramFetcher): Response
	{
		if (!$this->storePermissions->mayEditPickups($storeId)) {
			throw new HttpException(403);
		}

		$date = TimeHelper::parsePickupDate($pickupDate);
		if (is_null($date)) {
			throw new HttpException(400, 'Invalid date format');
		}

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
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Get("stores/{storeId}/pickups", requirements={"storeId" = "\d+"})
	 */
	public function listPickupsAction(int $storeId): Response
	{
		if (!$this->storePermissions->maySeePickups($storeId)) {
			throw new HttpException(403);
		}
		if (CarbonInterval::hours(Carbon::today()->diffInHours(Carbon::now()))->greaterThanOrEqualTo(CarbonInterval::hours(6))) {
			$fromTime = Carbon::today();
		} else {
			$fromTime = Carbon::today()->subHours(6);
		}

		$pickups = $this->pickupGateway->getPickupSlots($storeId, $fromTime);

		return $this->handleView($this->view([
			'pickups' => $this->enrichPickupSlots($pickups, $storeId)
		]));
	}

	/**
	 * @OA\Tag(name="pickup")
	 *
	 * @Rest\Get("stores/{storeId}/history/{fromDate}/{toDate}", requirements={"storeId" = "\d+", "fromDate" = "[^/]+", "toDate" = "[^/]+"})
	 */
	public function listPickupHistoryAction(int $storeId, string $fromDate, string $toDate): Response
	{
		if (!$this->storePermissions->maySeePickupHistory($storeId)) {
			throw new HttpException(403);
		}
		// convert date strings into datetime objects
		$from = TimeHelper::parsePickupDate($fromDate);
		$to = TimeHelper::parsePickupDate($toDate);
		if (is_null($from) || is_null($to)) {
			throw new HttpException(400, 'Invalid date format');
		}
		$from = $from->min(Carbon::now());
		$to = $to->min(Carbon::now());

		$pickups = [[
			'occupiedSlots' => $this->pickupGateway->getPickupHistory($storeId, $from, $to)
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
