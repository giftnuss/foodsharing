<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Services\StoreService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PickupRestController extends AbstractFOSRestController
{
	private $session;
	private $storePermissions;
	private $storeService;
	private $storeGateway;

	public function __construct(Session $session, StoreGateway $storeGateway, StorePermissions $storePermissions, StoreService $storeService)
	{
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

		$date = \DateTime::createFromFormat(DATE_ATOM, $pickupDate);
		if (!$date) {
			throw new HttpException(400, 'Invalid date format');
		}
		if (!$this->storeService->signupForPickup($this->session->id(), $storeId, $date, $this->storePermissions->hasPreconfirmedPickup($storeId))) {
			throw new HttpException(400, 'No pickup slot available');
		}

		return $this->handleView($this->view([], 200));
	}
}
