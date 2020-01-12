<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Profile\ProfileGateway;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Foodsharing\Permissions\ReportPermissions;

class ProfileRestController extends AbstractFOSRestController
{
	private $session;
	private $profileGateway;
	private $reportPermissions;

	public function __construct(Session $session, ProfileGateway $profileGateway, ReportPermissions $reportPermissions)
	{
		$this->session = $session;
		$this->profileGateway = $profileGateway;
		$this->reportPermissions = $reportPermissions;
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
		$profile = $this->profileGateway->getData($fs_id, $this->reportPermissions->mayHandleReports());

		return $this->handleView($this->view([
			'id' => $profile['id'],
			'name' => $profile['name'],
			'lastname' => $profile['nachname'],
			'address' => $profile['anschrift'],
			'city' => $profile['stadt'],
			'postcode' => $profile['plz'],
			'lat' => $profile['lat'],
			'lon' => $profile['lon'],
			'email' => $profile['email'],
			'landline' => $profile['telefon'],
			'mobile' => $profile['handy'],
		], 200));
	}
}
