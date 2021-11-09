<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\WorkGroup\WorkGroupGateway;
use Foodsharing\Modules\WorkGroup\WorkGroupTransactions;
use Foodsharing\Permissions\WorkGroupPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WorkingGroupRestController extends AbstractFOSRestController
{
	private WorkGroupGateway $workGroupGateway;
	private Session $session;
	private WorkGroupPermissions $workGroupPermissions;
	private WorkGroupTransactions $workGroupTransactions;

	public function __construct(
		WorkGroupGateway $workGroupGateway,
		Session $session,
		WorkGroupPermissions $workGroupPermissions,
		WorkGroupTransactions $workGroupTransactions
	) {
		$this->workGroupGateway = $workGroupGateway;
		$this->session = $session;
		$this->workGroupPermissions = $workGroupPermissions;
		$this->workGroupTransactions = $workGroupTransactions;
	}

	/**
	 * @Rest\Delete("groups/{groupId}/members/{memberId}", requirements={"groupId" = "\d+", "memberId" = "\d+"})
	 */
	public function removeMember(int $groupId, int $memberId): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		$group = $this->workGroupGateway->getGroup($groupId);
		if (empty($group)) {
			throw new HttpException(404);
		}

		if (!$this->workGroupPermissions->mayEdit($group)) {
			throw new HttpException(403);
		}

		$this->workGroupTransactions->removeMemberFromGroup($groupId, $memberId);

		return $this->handleView($this->view([], 200));
	}
}
