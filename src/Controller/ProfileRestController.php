<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Profile\ProfileGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProfileRestController extends AbstractFOSRestController
{
	private $session;
	private $profileGateway;

	public function __construct(Session $session, ProfileGateway $profileGateway)
	{
		$this->session = $session;
		$this->profileGateway = $profileGateway;
	}

	/**
	 * @Rest\Get("profile/current")
	 */
	public function currentProfileAction(): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(404);
		}

		$fs_id = $this->session->id();
		$this->profileGateway->setFsId($fs_id);
		$profile = $this->profileGateway->getData($fs_id);

		return $this->handleView($this->view([
			'id' => $profile['id'],
			'bezirk_id' => $profile['bezirk_id'],
			'plz' => $profile['plz'],
			'stadt' => $profile['stadt'],
			'lat' => $profile['lat'],
			'lon' => $profile['lon'],
			'email' => $profile['email'],
			'name' => $profile['name'],
			'nachname' => $profile['nachname'],
			'anschrift' => $profile['anschrift'],
			'telefon' => $profile['telefon'],
			'handy' => $profile['handy'],
			'photo' => $profile['photo'],
		], 200));
	}
}
