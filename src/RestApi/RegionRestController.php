<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Settings\SettingsGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\Utility\ImageHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RegionRestController extends AbstractFOSRestController
{
	private BellGateway $bellGateway;
	private FoodsaverGateway $foodsaverGateway;
	private RegionGateway $regionGateway;
	private StoreGateway $storeGateway;
	private RegionPermissions $regionPermissions;
	private Session $session;
	private ImageHelper $imageHelper;
	private SettingsGateway $settingsGateway;

	public function __construct(
		SettingsGateway $settingsGateway,
		BellGateway $bellGateway,
		FoodsaverGateway $foodsaverGateway,
		RegionPermissions $regionPermissions,
		RegionGateway $regionGateway,
		StoreGateway $storeGateway,
		Session $session,
		ImageHelper $imageHelper
	) {
		$this->settingsGateway = $settingsGateway;
		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionPermissions = $regionPermissions;
		$this->regionGateway = $regionGateway;
		$this->storeGateway = $storeGateway;
		$this->session = $session;
		$this->imageHelper = $imageHelper;
	}

	/**
	 * @Rest\Post("region/{regionId}/join", requirements={"regionId" = "\d+"})
	 */
	public function joinRegionAction(int $regionId): Response
	{
		$region = $this->regionGateway->getRegion($regionId);
		if (!$region) {
			throw new HttpException(404);
		}
		if (!$this->regionPermissions->mayJoinRegion($regionId)) {
			throw new HttpException(403);
		}

		$sessionId = $this->session->id();
		if ($sessionId === null) {
			throw new AccessDeniedHttpException();
		}

		$this->regionGateway->linkBezirk($sessionId, $regionId);

		if (!$this->session->getCurrentRegionId()) {
			$this->settingsGateway->logChangedSetting($sessionId, ['bezirk_id' => 0], ['bezirk_id' => $regionId], ['bezirk_id']);
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
			$this->imageHelper->img($foodsaver['photo'], 50),
			['href' => '/profile/' . (int)$sessionId . ''],
			[
				'name' => $foodsaver['name'] . ' ' . $foodsaver['nachname'],
				'bezirk' => $region['name']
			],
			'new-fs-' . $sessionId,
			true
		);
		$this->bellGateway->addBell($welcomeBellRecipients, $bellData);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Removes the current user from a region. Returns 403 if not logged in, 400 if the region does not exist, 409 if
	 * the user is still an active store manager in the region, or 200 if the user was removed from the region or was
	 * not a member of that region. That means that after a 200 result the user will definitely not be a member of that
	 * region anymore.
	 *
	 * @OA\Tag(name="region")
	 * @OA\Parameter(name="regionId", in="path", @OA\Schema(type="integer"), description="which region or group to leave")
	 * @OA\Response(response="200", description="Success")
	 * @OA\Response(response="400", description="Region or group does not exist")
	 * @OA\Response(response="403", description="Insufficient permissions")
	 * @OA\Response(response="409", description="User is still an active store manager in the region")
	 * @Rest\Post("region/{regionId}/leave", requirements={"regionId" = "\d+"})
	 */
	public function leaveRegionAction(int $regionId): Response
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

		$this->foodsaverGateway->deleteFromRegion($regionId, $this->session->id(), $this->session->id());

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Updates the master region for the given region and all its subregions.
	 *
	 * @OA\Tag(name="region")
	 * @OA\Parameter(name="regionId", in="path", @OA\Schema(type="integer"), description="the region that will be updated")
	 * @OA\Response(response="200", description="success")
	 * @OA\Response(response="403", description="Insufficient permissions")
	 * @Rest\Patch("region/{regionId}/masterupdate", requirements={"regionId" = "\d+"})
	 */
	public function masterUpdateAction(int $regionId): Response
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
