<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\WorkGroup\WorkGroupGateway;
use Foodsharing\Modules\WorkGroup\WorkGroupTransactions;
use Foodsharing\Permissions\WorkGroupPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WorkingGroupRestController extends AbstractFOSRestController
{
	private WorkGroupGateway $workGroupGateway;
	private FoodsaverGateway $foodsaverGateway;
	private Session $session;
	private WorkGroupPermissions $workGroupPermissions;
	private WorkGroupTransactions $workGroupTransactions;

	public function __construct(
		WorkGroupGateway $workGroupGateway,
		FoodsaverGateway $foodsaverGateway,
		Session $session,
		WorkGroupPermissions $workGroupPermissions,
		WorkGroupTransactions $workGroupTransactions
	) {
		$this->workGroupGateway = $workGroupGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->session = $session;
		$this->workGroupPermissions = $workGroupPermissions;
		$this->workGroupTransactions = $workGroupTransactions;
	}

	/**
	 * Removes a member from a working group. If the user was not a member of the group, nothing happens.
	 *
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Response(response="403", description="Insufficient permissions")
	 * @OA\Response(response="404", description="Group not found")
	 * @OA\Tag(name="groups")
	 *
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

	/**
	 * Adds a member to a working group. If the user is already a member of the group, nothing happens.
	 *
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="401", description="Not logged in")
	 * @OA\Response(response="403", description="Insufficient permissions")
	 * @OA\Response(response="404", description="Group not found")
	 * @OA\Tag(name="groups")
	 *
	 * @Rest\Post  ("groups/{groupId}/members/{memberId}", requirements={"groupId" = "\d+", "memberId" = "\d+"})
	 */
	public function addMember(int $groupId, int $memberId): Response
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

		$this->workGroupGateway->addToGroup($groupId, $memberId);
		$user = RestNormalization::normalizeUser($this->foodsaverGateway->getFoodsaverBasics($memberId));

		return $this->handleView($this->view($user, 200));
	}
}
