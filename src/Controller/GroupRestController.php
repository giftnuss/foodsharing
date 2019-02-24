<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Group\GroupGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GroupRestController extends AbstractFOSRestController
{
	private $groupGateway;
	private $session;

	public function __construct(GroupGateway $groupGateway, Session $session)
	{
		$this->groupGateway = $groupGateway;
		$this->session = $session;
	}

	/**
	 * Delete a region or a working group.
	 *
	 * @Rest\Delete("groups/{groupId}", requirements={"groupId" = "\d+"})
	 */
	public function deleteGroupAction(int $groupId)
	{
		if (!$this->session->may('orga')) {
			throw new HttpException(403);
		}

		$this->groupGateway->deleteGroup($groupId);

		return $this->handleView($this->view([], 200));
	}
}
