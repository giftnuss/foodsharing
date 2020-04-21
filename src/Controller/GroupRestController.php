<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Group\GroupGateway;
use Foodsharing\Permissions\RegionPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GroupRestController extends AbstractFOSRestController
{
	private $groupGateway;
	private $session;
	private $regionPermissions;

	public function __construct(GroupGateway $groupGateway, Session $session, RegionPermissions $regionPermissions)
	{
		$this->groupGateway = $groupGateway;
		$this->session = $session;
		$this->regionPermissions = $regionPermissions;
	}

	/**
	 * Delete a region or a working group.
	 *
	 * @Rest\Delete("groups/{groupId}", requirements={"groupId" = "\d+"})
	 */
	public function deleteGroupAction(int $groupId)
	{
		/*
		* In some of these calls orga is still being checked against additionally, as this REST call is used with different modules but those modules don't have own permission classes yet.
		* Even tho orga is yet the only condition of mayAdministrateRegions().
		* This allows us to be flexible in case we want to remove this feature for most orgas. Which might be likely.
		* */
		if (!($this->session->may('orga') || $this->regionPermissions->mayAdministrateRegions())) {
			throw new HttpException(403);
		}

		$this->groupGateway->deleteGroup($groupId);

		return $this->handleView($this->view([], 200));
	}
}
