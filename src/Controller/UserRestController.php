<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Login\LoginGateway;
use Foodsharing\Permissions\ProfilePermissions;
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
	private $profilePermissions;

	public function __construct(Session $session, LoginGateway $loginGateway, FoodsaverGateway $foodsaverGateway, ProfilePermissions $profilePermissions)
	{
		$this->session = $session;
		$this->loginGateway = $loginGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->profilePermissions = $profilePermissions;
	}

	/**
	 * @Rest\Get("user/current")
	 */
	public function currentUserAction(): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(404);
		}

		return $this->handleUserView();
	}

	/**
	 * Lists details about a user. Returns 200 and the user data, 404 if the
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

		$normalized = RestNormalization::normalizeFoodsaver($data);

		return $this->handleView($this->view($normalized, 200));
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

			return $this->handleUserView();
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
		$user = $this->session->get('user');

		return $this->handleView($this->view([
			'id' => $this->session->id(),
			'name' => $user['name']
		], 200));
	}
}
