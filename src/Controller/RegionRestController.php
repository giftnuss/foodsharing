<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\Utility\ImageHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RegionRestController extends AbstractFOSRestController
{
	private $bellGateway;
	private $foodsaverGateway;
	private $regionGateway;
	private $storeGateway;
	private $regionPermissions;
	private $session;
	private $imageService;

	public function __construct(
		BellGateway $bellGateway,
		FoodsaverGateway $foodsaverGateway,
		RegionPermissions $regionPermissions,
		RegionGateway $regionGateway,
		StoreGateway $storeGateway,
		Session $session,
		ImageHelper $imageService
	) {
		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionPermissions = $regionPermissions;
		$this->regionGateway = $regionGateway;
		$this->storeGateway = $storeGateway;
		$this->session = $session;
		$this->imageService = $imageService;
	}

	/**
	 * @Rest\Post("region/{regionId}/join", requirements={"regionId" = "\d+"})
	 */
	public function joinRegionAction($regionId)
	{
		if (!$this->regionGateway->getRegion($regionId)) {
			throw new HttpException(404);
		}
		if (!$this->regionPermissions->mayJoinRegion($regionId)) {
			throw new HttpException(403);
		}

		$region = $this->regionGateway->getRegion($regionId);

		$sessionId = $this->session->id();

		$this->regionGateway->linkBezirk($sessionId, $regionId);

		if (!$this->session->getCurrentRegionId()) {
			$this->foodsaverGateway->updateProfile($sessionId, ['bezirk_id' => $regionId]);
		}

		$regionWelcomeGroupId = $this->regionGateway->getRegionWelcomeGroupId($regionId);
		if ($regionWelcomeGroupId) {
			$welcomeBellRecipients = $this->foodsaverGateway->getAdminsOrAmbassadors($regionWelcomeGroupId);
		} else {
			$welcomeBellRecipients = $this->foodsaverGateway->getAdminsOrAmbassadors($regionId);
		}

		$foodsaver = $this->session->get('user');
		$bellData = Bell::create(
			'new_foodsaver_title',
			$foodsaver['verified'] ? 'new_foodsaver_verified' : 'new_foodsaver',
			$this->imageService->img($foodsaver['photo'], 50),
			['href' => '/profile/' . (int)$sessionId . ''],
			[
				'name' => $foodsaver['name'] . ' ' . $foodsaver['nachname'],
				'bezirk' => $region['name']
			],
			'new-fs-' . $sessionId,
			true
		);
		$this->bellGateway->addBell($welcomeBellRecipients, $bellData);

		$view = $this->view([], 200);

		return $this->handleView($view);
	}

	/**
	 * Removes the current user from a region. Returns 403 if not logged in, 400 if the region does not exist, 409 if
	 * the user is still an active store manager in the region, or 200 if the user was removed from the region or was
	 * not a member of that region. That means that after a 200 result the user will definitely not be a member of that
	 * region anymore.
	 *
	 * @SWG\Tag(name="region")
	 * @SWG\Parameter(name="regionId", in="path", type="integer", description="which region or group to leave")
	 * @SWG\Response(response="200", description="Success")
	 * @SWG\Response(response="400", description="Region or group does not exist")
	 * @SWG\Response(response="403", description="Insufficient permissions")
	 * @SWG\Response(response="409", description="User is still an active store manager in the region")
	 * @Rest\Post("region/{regionId}/leave", requirements={"regionId" = "\d+"})
	 */
	public function leaveRegionAction($regionId)
	{
		if (!$this->session->may()) {
			throw new AccessDeniedHttpException();
		}

		if (empty($this->regionGateway->getRegion($regionId))) {
			throw new HttpException(400, 'region does not exist or is root region.');
		}

		if (in_array($this->session->id(), $this->storeGateway->getStoreManagersOf($regionId))) {
			throw new HttpException(409, 'still an active store manager in that region');
		}

		$this->foodsaverGateway->deleteFromRegion($regionId, $this->session->id());

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Updates the master region for the given region and all its subregions.
	 *
	 * @SWG\Tag(name="region")
	 * @SWG\Parameter(name="regionId", in="path", type="integer", description="the region that will be updated")
	 * @SWG\Response(response="200", description="success")
	 * @SWG\Response(response="403", description="Insufficient permissions")
	 * @Rest\Patch("region/{regionId}/masterupdate", requirements={"regionId" = "\d+"})
	 */
	public function masterUpdateAction(int $regionId)
	{
		if (!$this->regionPermissions->mayAdministrateRegions()) {
			throw new HttpException(403);
		}

		if ($regions = $this->regionGateway->listIdsForDescendantsAndSelf($regionId)) {
			$this->regionGateway->updateMasterRegions($regions, $regionId);
		}

		return $this->handleView($this->view([], 200));
	}
}
