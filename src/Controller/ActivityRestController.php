<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Activity\ActivityGateway;
use Foodsharing\Modules\Activity\ActivityTransactions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ActivityRestController extends AbstractFOSRestController
{
	private ActivityTransactions $activityTransactions;
	private ActivityGateway $activityGateway;
	private Session $session;

	public function __construct(
		ActivityTransactions $activityTransactions,
		ActivityGateway $activityGateway,
		Session $session
	) {
		$this->activityTransactions = $activityTransactions;
		$this->activityGateway = $activityGateway;
		$this->session = $session;
	}

	/**
	 * Returns the options for all activities of this user that can be turned on or off.
	 *
	 * @SWG\Response(response="200", description="Success.")
	 * @SWG\Response(response="403", description="Insufficient permissions to request the options.")
	 * @SWG\Tag(name="tag")
	 *
	 * @Rest\Get("activities/options")
	 */
	public function getActivityOptionsAction(): Response
	{
		if (!$this->session->id()) {
			throw new HttpException(403);
		}

		$options = $this->activityTransactions->getOptions();

		return $this->handleView($this->view($options, 200));
	}
}
