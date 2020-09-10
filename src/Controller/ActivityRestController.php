<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Activity\ActivityGateway;
use Foodsharing\Modules\Activity\ActivityTransactions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
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
	 * Returns the options for all dashboard activities for the current user.
	 *
	 * @SWG\Response(response="200", description="Success.")
	 * @SWG\Response(response="403", description="Insufficient permissions to request the options.")
	 * @SWG\Tag(name="activities")
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

	/**
	 * Sets which dashboard activities should be deactivated for the current user.
	 *
	 * @SWG\Response(response="200", description="Success.")
	 * @SWG\Response(response="403", description="Insufficient permissions to set options.")
	 * @SWG\Tag(name="activities")
	 *
	 * @Rest\Patch("activities/options")
	 * @Rest\RequestParam(name="options")
	 */
	public function setActivityOptionsAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new HttpException(403);
		}

		$options = $paramFetcher->get('options');
		$this->activityTransactions->setOptions($options);

		return $this->handleView($this->view([], 200));
	}
}
