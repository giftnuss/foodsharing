<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Permissions\RegionPermissions;
use Foodsharing\Services\ImageService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RegionRestController extends AbstractFOSRestController
{
	private $bellGateway;
	private $foodsaverGateway;
	private $regionGateway;
	private $regionPermissions;
	private $session;
	private $imageService;

	public function __construct(
		BellGateway $bellGateway,
		FoodsaverGateway $foodsaverGateway,
		RegionPermissions $regionPermissions,
		RegionGateway $regionGateway,
		Session $session,
		ImageService $imageService
	) {
		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->regionPermissions = $regionPermissions;
		$this->regionGateway = $regionGateway;
		$this->session = $session;
		$this->imageService = $imageService;
	}

	/**
	 * @Rest\Post("region/{regionId}/join", requirements={"regionId" = "\d+"})
	 */
	public function joinRegionAction($regionId)
	{
		if ($region = $this->regionGateway->getBezirk($regionId)) {
			if ($this->regionPermissions->mayJoinRegion($regionId)) {
				$this->regionGateway->linkBezirk($this->session->id(), $regionId);

				if (!$this->session->getCurrentBezirkId()) {
					$this->foodsaverGateway->updateProfile($this->session->id(), ['bezirk_id' => $regionId]);
				}

				$bots = $this->foodsaverGateway->getBotschafter($regionId);
				$foodsaver = $this->session->get('user');
				$this->bellGateway->addBell(
					$bots,
					'new_foodsaver_title',
					$foodsaver['verified'] ? 'new_foodsaver_verified' : 'new_foodsaver',
					$this->imageService->img($foodsaver['photo'], 50),
					array('href' => '/profile/' . (int)$this->session->id() . ''),
					array(
						'name' => $foodsaver['name'] . ' ' . $foodsaver['nachname'],
						'bezirk' => $region['name']
					),
					'new-fs-' . $this->session->id(),
					true
				);

				$view = $this->view([], 200);

				return $this->handleView($view);
			} else {
				throw new HttpException(403);
			}
		} else {
			throw new HttpException(404);
		}
	}
}
