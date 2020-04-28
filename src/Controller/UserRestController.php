<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Login\LoginGateway;
use Foodsharing\Modules\Profile\ProfileGateway;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Permissions\ReportPermissions;
use Foodsharing\Permissions\UserPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Mobile_Detect;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserRestController extends AbstractFOSRestController
{
	private $session;
	private $loginGateway;
	private $foodsaverGateway;
	private $profileGateway;
	private $reportPermissions;
	private $userPermissions;
	private $profilePermissions;

	public function __construct(
		Session $session,
		LoginGateway $loginGateway,
		FoodsaverGateway $foodsaverGateway,
		ProfileGateway $profileGateway,
		ReportPermissions $reportPermissions,
		UserPermissions $userPermissions,
		ProfilePermissions $profilePermissions)
	{
		$this->session = $session;
		$this->loginGateway = $loginGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->profileGateway = $profileGateway;
		$this->reportPermissions = $reportPermissions;
		$this->userPermissions = $userPermissions;
		$this->profilePermissions = $profilePermissions;
	}

	/**
	 * Checks if the user is logged in and lists the basic user information. Returns 200 and the user data, 404 if the
	 * user does not exist, or 401 if not logged in.
	 *
	 * @Rest\Get("user/{id}", requirements={"id" = "\d+"})
	 */
	public function userAction(int $id): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		$data = $this->foodsaverGateway->getFoodsaverBasics($id);
		if (!$data || empty($data)) {
			throw new HttpException(404, 'User does not exist.');
		}

		return $this->handleView($this->view(RestNormalization::normalizeUser($data), 200));
	}

	/**
	 * Checks if the user is logged in  and lists the basic user information. Returns 401 if not logged in or 200 and
	 * the user data.
	 *
	 * @Rest\Get("user/current")
	 */
	public function currentUserAction(): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		return $this->userAction($this->session->id());
	}

	/**
	 * Lists the detailed profile of a user. Returns 403 if not allowed or 200 and the data.
	 *
	 * @Rest\Get("user/{id}/details", requirements={"id" = "\d+"})
	 */
	public function userDetailsAction(int $id): Response
	{
		if (!$this->userPermissions->maySeeUserDetails($id)) {
			throw new HttpException(403);
		}

		$data = $this->profileGateway->getData($id, -1, $this->reportPermissions->mayHandleReports());
		if (!$data || empty($data)) {
			throw new HttpException(404, 'User does not exist.');
		}

		return $this->handleView($this->view(RestNormalization::normaliseUserDetails($data), 200));
	}

	/**
	 * Lists the detailed profile of the current user. Returns 401 if not logged in or 200 and the data.
	 *
	 * @Rest\Get("user/current/details")
	 */
	public function currentUserDetailsAction(): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		return $this->userDetailsAction($this->session->id());
	}

	/**
	 * @Rest\Post("user/login")
	 * @Rest\RequestParam(name="email")
	 * @Rest\RequestParam(name="password")
	 * @Rest\RequestParam(name="remember_me", default=false)
	 */
	public function loginAction(ParamFetcher $paramFetcher): Response
	{
		$email = $paramFetcher->get('email');
		$password = $paramFetcher->get('password');
		$rememberMe = (bool)$paramFetcher->get('remember_me');
		$fs_id = $this->loginGateway->login($email, $password);
		if ($fs_id) {
			$this->session->login($fs_id, $rememberMe);

			$mobdet = new Mobile_Detect();
			if ($mobdet->isMobile()) {
				$_SESSION['mob'] = 1;
			}

			// retrieve user data and normalise it
			$user = $this->foodsaverGateway->getFoodsaverBasics($fs_id);
			if (!$user || empty($user)) {
				throw new HttpException(404, 'User does not exist.');
			}
			$normalizedUser = RestNormalization::normalizeUser($user);

			return $this->handleView($this->view($normalizedUser, 200));
		}

		throw new HttpException(401, 'email or password are invalid');
	}

	/**
	 * @Rest\Post("user/logout")
	 */
	public function logoutAction(): Response
	{
		$this->session->logout();

		return $this->handleView($this->view([], 200));
	}

	/**
	 * @Rest\Delete("user/{userId}", requirements={"userId" = "\d+"})
	 */
	public function deleteUserAction(int $userId): Response
	{
		if ($userId !== $this->session->id() && !$this->profilePermissions->mayDeleteUser()) {
			throw new HttpException(403);
		}

		if ($userId === $this->session->id()) {
			$this->session->logout();
		}
		$this->foodsaverGateway->deleteFoodsaver($userId);

		return $this->handleView($this->view());
	}

	private function handleUserView(): Response
	{
		$user = RestNormalization::normalizeUser([
			'id' => $this->session->id(),
			'name' => $this->session->get('user')['name']
		]);

		return $this->handleView($this->view($user, 200));
	}
}
