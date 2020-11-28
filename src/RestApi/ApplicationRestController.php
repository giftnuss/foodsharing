<?php

namespace Foodsharing\RestApi;

use Exception;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Application\ApplicationTransactions;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\WorkGroupPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
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
	 * @OA\Tag(name="application")
	 * @OA\Parameter(name="groupId", in="path", @OA\Schema(type="integer"), description="which work group the request is for")
	 * @OA\Response(response="200", description="success")
	 * @OA\Response(response="403", description="Insufficient permissions")
	 * @OA\Response(response="404", description="Workgroup does not exist.")
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

	/**
	 * Declines an application for a work group.
	 *
	 * @OA\Tag(name="application")
	 * @OA\Parameter(name="groupId", in="path", @OA\Schema(type="integer"), description="which work group the request is for")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="403", description="Insufficient permissions")
	 * @OA\Response(response="404", description="Workgroup does not exist.")
	 *
	 * @Rest\Delete("applications/{groupId}/{userId}", requirements={"groupId" = "\d+", "userId" = "\d+"})
	 */
	public function declineApplicationAction(int $groupId, int $userId): Response
	{
		try {
			$group = $this->regionGateway->getRegion($groupId);
		} catch (Exception $e) {
			throw new HttpException(404);
		}

		if (!$this->workGroupPermissions->mayEdit($group)) {
			throw new HttpException(403);
		}

		$this->applicationTransactions->declineApplication($group, $userId);

		return $this->handleView($this->view([], 200));
	}
}
