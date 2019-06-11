<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Store\StoreGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StoreRestController extends AbstractFOSRestController
{
	private $session;
	private $storeGateway;

	// literal constants
	private const NOT_LOGGED_IN = 'not logged in';
	private const ID = 'id';

	public function __construct(Session $session, StoreGateway $storeGateway)
	{
		$this->session = $session;
		$this->storeGateway = $storeGateway;
	}

	/**
	 * Returns details of the store with the given ID. Returns 200 and the
	 * store, 404 if the store does not exist, or 401 if not logged in.
	 *
	 * @Rest\Get("stores/{storeId}", requirements={"basketId" = "\d+"})
	 *
	 * @param int $storeId
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getStoreAction(int $storeId): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401, self::NOT_LOGGED_IN);
		}

		$store = $this->storeGateway->getBetrieb($storeId);

		if (!$store || !isset($store[self::ID])) {
			throw new HttpException(404, 'Store does not exist.');
		}

		$store = RestNormalization::normalizeStore($store);

		return $this->handleView($this->view(['store' => $store], 200));
	}
}
