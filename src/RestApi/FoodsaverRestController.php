<?php

namespace Foodsharing\RestApi;

use Carbon\Carbon;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Profile\ProfileGateway;
use Foodsharing\Modules\Store\PickupGateway;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Utility\TimeHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class FoodsaverRestController extends AbstractFOSRestController
{
	private FoodsaverGateway $foodsaverGateway;
	private ProfileGateway $profileGateway;
	private PickupGateway $pickupGateway;
	private ProfilePermissions $profilePermissions;
	private Session $session;

	public function __construct(
		FoodsaverGateway $foodsaverGateway,
		ProfileGateway $profileGateway,
		PickupGateway $pickupGateway,
		ProfilePermissions $profilePermissions,
		Session $session
	) {
		$this->foodsaverGateway = $foodsaverGateway;
		$this->profileGateway = $profileGateway;
		$this->pickupGateway = $pickupGateway;
		$this->profilePermissions = $profilePermissions;
		$this->session = $session;
	}

	/**
	 * Lists all pickups into which a user is signed in on a specific day, including unconfirmed ones.
	 * This only works for future pickups.
	 *
	 * @Rest\Get("foodsaver/{fsId}/pickups/{onDate}", requirements={"fsId" = "\d+", "onDate" = "[^/]+"})
	 */
	public function listSameDayPickupsAction(int $fsId, string $onDate): Response
	{
		if (!$this->session->id() || !$this->profilePermissions->maySeePickups($fsId)) {
			throw new HttpException(403);
		}

		// convert date string into datetime object
		$day = TimeHelper::parsePickupDate($onDate);
		if (is_null($day)) {
			throw new HttpException(400, 'Invalid date format');
		}
		$pickups = $this->pickupGateway->getSameDayPickupsForUser($fsId, $day);

		return $this->handleView($this->view($pickups));
	}

	/**
	 * @Rest\Get("foodsaver/{fsId}/pickups/{fromDate}/{toDate}", requirements={"fsId" = "\d+", "fromDate" = "[^/]+", "toDate" = "[^/]+"})
	 */
	public function listPastPickupsAction(int $fsId, string $fromDate, string $toDate): Response
	{
		if (!$this->session->id() || !$this->profilePermissions->maySeePickups($fsId)) {
			throw new HttpException(403);
		}

		// convert date strings into datetime objects
		$from = TimeHelper::parsePickupDate($fromDate);
		$to = TimeHelper::parsePickupDate($toDate);
		if (is_null($from) || is_null($to)) {
			throw new HttpException(400, 'Invalid date format');
		}
		$from = $from->min(Carbon::now())->max(Carbon::now()->subMonth());
		$to = $to->min(Carbon::now());

		$pickups = [
			['occupiedSlots' => $this->profileGateway->getRecentPickups($fsId, $from, $to)],
		];

		return $this->handleView($this->view([
			'pickups' => $this->enrichPickupSlots($pickups),
		]));
	}

	/**
	 * @deprecated This is a (less generic) duplicate of PickupRestController:enrichPickupSlots.
	 *
	 * It should be removed soon, or combined into a RestNormalization or DTO.
	 * Right now, this is not possible because of the foodsaverGateway coupling!
	 */
	private function enrichPickupSlots(array $pickups): array
	{
		foreach ($pickups as &$pickup) {
			foreach ($pickup['occupiedSlots'] as &$slot) {
				$details = $this->foodsaverGateway->getFoodsaver($slot['foodsaverId']);
				$slot['profile'] = RestNormalization::normalizeUser($details);
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
