<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Activity\ActivityGateway;
use Foodsharing\Modules\Activity\ActivityTransactions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use OpenApi\Annotations as OA;
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
	 * Returns the filters for all dashboard activities for the current user.
	 *
	 * @OA\Response(response="200", description="Success.")
	 * @OA\Response(response="403", description="Insufficient permissions to request filters.")
	 * @OA\Tag(name="activities")
	 *
	 * @Rest\Get("activities/filters")
	 */
	public function getActivityFiltersAction(): Response
	{
		if (!$this->session->id()) {
			throw new HttpException(403);
		}

		$filters = $this->activityTransactions->getFilters();

		return $this->handleView($this->view($filters, 200));
	}

	/**
	 * Sets which dashboard activities should be deactivated for the current user.
	 *
	 * @OA\Response(response="200", description="Success.")
	 * @OA\Response(response="403", description="Insufficient permissions to set filters.")
	 * @OA\Tag(name="activities")
	 *
	 * @Rest\Patch("activities/filters")
	 * @Rest\RequestParam(name="excluded")
	 */
	public function setActivityFiltersAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->id()) {
			throw new HttpException(403);
		}

		$excluded = $paramFetcher->get('excluded');
		$this->activityTransactions->setExcludedFilters($excluded);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Returns the updates object for <ActivityOverview> to display on the dashboard.
	 *
	 * @OA\Response(response="200", description="Success.")
	 * @OA\Tag(name="activities")
	 *
	 * @Rest\Get("activities/updates")
	 * @Rest\QueryParam(name="page", requirements="\d+", default="0", description="Which page of updates to return")
	 */
	public function getActivityUpdatesAction(ParamFetcher $paramFetcher): Response
	{
		$page = intval($paramFetcher->get('page'));

		$updates = [
			'updates' => $this->activityTransactions->getUpdateData($page),
		];

		return $this->handleView($this->view($updates, 200));
	}
}
