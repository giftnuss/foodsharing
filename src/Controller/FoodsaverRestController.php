<?php

namespace Foodsharing\Controller;

use Carbon\Carbon;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Profile\ProfileGateway;
use Foodsharing\Utility\TimeHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class FoodsaverRestController extends AbstractFOSRestController
{
	private ProfileGateway $profileGateway;
	private PickupRestController $pickupRestController;
	private Session $session;

	public function __construct(
		ProfileGateway $profileGateway,
		PickupRestController $pickupRestController,
		Session $session
	) {
		$this->profileGateway = $profileGateway;
		$this->pickupRestController = $pickupRestController;
		$this->session = $session;
	}

	/**
	 * @Rest\Get("foodsaver/{fsId}/pickups/{fromDate}/{toDate}", requirements={"fsId" = "\d+", "fromDate" = "[^/]+", "toDate" = "[^/]+"})
	 */
	public function listPastPickupsAction(int $fsId, string $fromDate, string $toDate): Response
	{
		// convert date strings into datetime objects
		$from = TimeHelper::parsePickupDate($fromDate);
		$to = TimeHelper::parsePickupDate($toDate);
		if (is_null($from) || is_null($to)) {
			throw new HttpException(400, 'Invalid date format');
		}
		$from = $from->min(Carbon::now());
		$to = $to->min(Carbon::now());

		$pickups = [
			['occupiedSlots' => $this->profileGateway->getRecentPickups($fsId, $from, $to)],
		];

		return $this->handleView($this->view([
			'pickups' => $this->pickupRestController->enrichPickupSlots($pickups),
		]));
	}
}
