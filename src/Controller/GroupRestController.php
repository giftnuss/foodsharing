<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\BigBlueButton;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Group\GroupGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\RegionPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
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

	/**
	 * Returns the join URL of a given groups conference.
	 *
	 * @Rest\Get("groups/{groupId}/conference", requirements={"groupId" = "\d+"})
	 * @Rest\QueryParam(name="redirect", default="false", description="Should the response perform a 301 redirect to the actual conference?")
	 */
	public function joinConferenceAction(RegionGateway $regionGateway, RegionPermissions $regionPermissions, BigBlueButton $bbb, int $groupId, ParamFetcher $paramFetcher)
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}
		if (!in_array($groupId, $this->session->listRegionIDs())) {
			throw new HttpException(403);
		}
		$group = $regionGateway->getRegion($groupId);
		if (!$regionPermissions->hasConference($group['type'])) {
			throw new HttpException(403, 'This region does not support conferences');
		}
		$key = 'region-' . $groupId;
		$conference = $bbb->createRoom($group['name'], $key);
		if (!$conference) {
			throw new HttpException(500, 'Conferences currently not available');
		}
		$data = [
			'dialin' => $conference['dialin'],
			'id' => $conference['id'],
		];
		/* We do a 301 redirect directly to have less likeliness that the user forwards the BBB join URL as this is already personalized */
		if ($paramFetcher->get('redirect') == 'true') {
			return $this->redirect($bbb->joinURL($key, $this->session->user('name'), true));
		}
		/* Without the redirect, we return information about the conference */
		return $this->handleView($this->view($data, 200));
	}
}
