<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Profile\ProfileGateway;
use Foodsharing\Permissions\ProfilePermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VerificationRestController extends AbstractFOSRestController
{
	private BellGateway $bellGateway;
	private FoodsaverGateway $foodsaverGateway;
	private ProfileGateway $profileGateway;
	private ProfilePermissions $profilePermissions;
	private Session $session;

	public function __construct(
		BellGateway $bellGateway,
		FoodsaverGateway $foodsaverGateway,
		ProfileGateway $profileGateway,
		ProfilePermissions $profilePermissions,
		Session $session
	) {
		$this->bellGateway = $bellGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->profileGateway = $profileGateway;
		$this->profilePermissions = $profilePermissions;
		$this->session = $session;
	}

	/**
	 * Changes verification status of one user to 'verified'.
	 *
	 * @SWG\Parameter(name="userId", in="path", type="integer", description="which user to verify")
	 * @SWG\Response(response="200", description="Success.")
	 * @SWG\Response(response="401", description="Not logged in.")
	 * @SWG\Response(response="403", description="Insufficient permissions to verify this user.")
	 * @SWG\Response(response="404", description="User not found.")
	 * @SWG\Response(response="422", description="Already verified.")
	 *
	 * @Rest\Patch("user/{userId}/verify", requirements={"userId" = "\d+"})
	 */
	public function verifyUserAction(int $userId): Response
	{
		$sessionId = $this->session->id();
		if (!$sessionId) {
			throw new HttpException(401);
		}

		if (!$this->profilePermissions->mayChangeUserVerification($userId)) {
			throw new HttpException(403);
		}

		if ($this->profileGateway->isUserVerified($userId)) {
			throw new HttpException(422, 'User is already verified');
		}

		$this->foodsaverGateway->changeUserVerification($userId, $sessionId, true);
		// TODO perhaps move this into a separate transaction?
		$this->bellGateway->delBellsByIdentifier('new-fs-' . $userId);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Changes verification status of one user to 'deverified'.
	 *
	 * @SWG\Parameter(name="userId", in="path", type="integer", description="which user to deverify")
	 * @SWG\Response(response="200", description="Success.")
	 * @SWG\Response(response="400", description="Has future pickups.")
	 * @SWG\Response(response="401", description="Not logged in.")
	 * @SWG\Response(response="403", description="Insufficient permissions to deverify this user.")
	 * @SWG\Response(response="404", description="User not found.")
	 * @SWG\Response(response="422", description="Already deverified.")
	 *
	 * @Rest\Delete("user/{userId}/verify", requirements={"userId" = "\d+"})
	 */
	public function deverifyUserAction(int $userId): Response
	{
		$sessionId = $this->session->id();
		if (!$sessionId) {
			throw new HttpException(401);
		}

		if (!$this->profilePermissions->mayChangeUserVerification($userId)) {
			throw new HttpException(403);
		}

		if (!$this->profileGateway->isUserVerified($userId)) {
			throw new HttpException(422, 'User is already deverified');
		}

		$hasPlannedPickups = $this->profileGateway->getNextDates($userId, 1);
		if ($hasPlannedPickups) {
			throw new HttpException(400, 'This user must not be signed up for any future pickups.');
		}

		$this->foodsaverGateway->changeUserVerification($userId, $sessionId, false);

		return $this->handleView($this->view([], 200));
	}
}
