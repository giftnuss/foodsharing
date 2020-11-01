<?php

namespace Foodsharing\Controller;

use Carbon\Carbon;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Profile\ProfileGateway;
use Foodsharing\Utility\TimeHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class FoodsaverRestController extends AbstractFOSRestController
{
	private FoodsaverGateway $foodsaverGateway;
	private ProfileGateway $profileGateway;
	private Session $session;

	public function __construct(
		FoodsaverGateway $foodsaverGateway,
		ProfileGateway $profileGateway,
		Session $session
	) {
		$this->foodsaverGateway = $foodsaverGateway;
		$this->profileGateway = $profileGateway;
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
				$slot['profile'] = RestNormalization::normalizeStoreUser($details);
			}
			unset($slot['foodsaverId']);
		}
		unset($pickup);
		usort($pickups, function ($a, $b) {
			return $a['date']->lt($b['date']) ? -1 : 1;
		});

		return $pickups;
	}
}
