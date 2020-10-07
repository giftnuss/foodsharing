<?php

namespace Foodsharing\Controller;

use Exception;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Application\ApplicationTransactions;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\WorkGroupPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApplicationRestController extends AbstractFOSRestController
{
	private RegionGateway $regionGateway;
	private WorkGroupPermissions $workGroupPermissions;
	private ApplicationTransactions $applicationTransactions;
	private Session $session;

	public function __construct(
		WorkGroupPermissions $workGroupPermissions,
		RegionGateway $regionGateway,
		ApplicationTransactions $applicationTransactions,
		Session $session
	) {
		$this->workGroupPermissions = $workGroupPermissions;
		$this->regionGateway = $regionGateway;
		$this->applicationTransactions = $applicationTransactions;
		$this->session = $session;
	}

	/**
	 * Accepts an application for a work group.
	 *
	 * @SWG\Tag(name="application")
	 * @SWG\Parameter(name="groupId", in="path", type="integer", description="which work group the request is for")
	 * @SWG\Response(response="200", description="success")
	 * @SWG\Response(response="403", description="Insufficient permissions")
	 * @SWG\Response(response="404", description="Workgroup does not exist.")
	 *
	 * @Rest\Patch("applications/{groupId}/{userId}", requirements={"groupId" = "\d+", "userId" = "\d+"})
	 */
	public function acceptApplicationAction(int $groupId, int $userId): Response
	{
		try {
			$group = $this->regionGateway->getRegion($groupId);
		} catch (Exception $e) {
			throw new HttpException(404);
		}

		if (!$this->workGroupPermissions->mayEdit($group)) {
			throw new HttpException(403);
		}

		$this->applicationTransactions->acceptApplication($group, $userId);

		return $this->handleView($this->view([], 200));
	}
}
