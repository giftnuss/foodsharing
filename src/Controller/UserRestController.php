<?php

namespace Foodsharing\Controller;

use Carbon\Carbon;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Gender;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Login\LoginGateway;
use Foodsharing\Modules\Profile\ProfileGateway;
use Foodsharing\Permissions\ProfilePermissions;
use Foodsharing\Permissions\ReportPermissions;
use Foodsharing\Permissions\UserPermissions;
use Foodsharing\Utility\EmailHelper;
use Foodsharing\Utility\RegisterTransactions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Mobile_Detect;
use Swagger\Annotations as SWG;
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
	private $emailHelper;
	private $registerTransactions;

	private const MIN_RATING_MESSAGE_LENGTH = 100;
	private const MIN_PASSWORD_LENGTH = 8;
	private const MIN_AGE_YEARS = 18;

	public function __construct(
		Session $session,
		LoginGateway $loginGateway,
		FoodsaverGateway $foodsaverGateway,
		ProfileGateway $profileGateway,
		ReportPermissions $reportPermissions,
		UserPermissions $userPermissions,
		ProfilePermissions $profilePermissions,
		EmailHelper $emailHelper,
		RegisterTransactions $registerTransactions
	) {
		$this->session = $session;
		$this->loginGateway = $loginGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->profileGateway = $profileGateway;
		$this->reportPermissions = $reportPermissions;
		$this->userPermissions = $userPermissions;
		$this->profilePermissions = $profilePermissions;
		$this->emailHelper = $emailHelper;
		$this->registerTransactions = $registerTransactions;
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
	 * Tests if an email address is valid for registration. Returns 400 if the parameter is not an email address or 200
	 * and a 'valid' parameter that indicates if the email address can be used for registration.
	 *
	 * @Rest\Post("user/isvalidemail")
	 * @Rest\RequestParam(name="email", nullable=false)
	 */
	public function testRegisterEmailAction(ParamFetcher $paramFetcher): Response
	{
		$email = $paramFetcher->get('email');
		if (empty($email) || !$this->emailHelper->validEmail($email)) {
			throw new HttpException(400, 'email is not valid');
		}

		return $this->handleView($this->view([
			'valid' => $this->isEmailValidForRegistration($email)
		], 200));
	}

	/**
	 * @Rest\Post("user")
	 * @Rest\RequestParam(name="firstname", nullable=false)
	 * @Rest\RequestParam(name="lastname", nullable=false)
	 * @Rest\RequestParam(name="email", nullable=false)
	 * @Rest\RequestParam(name="password", nullable=false)
	 * @Rest\RequestParam(name="gender", nullable=false, requirements="\d+")
	 * @Rest\RequestParam(name="birthday", nullable=false)
	 * @Rest\RequestParam(name="mobilePhone, nullable=true)
	 * @Rest\RequestParam(name="subscribeNewsletter, requirements="(0|1)", default=0)
	 */
	public function registerUserAction(ParamFetcher $paramFetcher): Response
	{
		// validate data
		$data = [];
		$data['name'] = trim(strip_tags($paramFetcher->get('firstname')));
		$data['surname'] = trim(strip_tags($paramFetcher->get('lastname')));
		if (empty($data['name']) || empty($data['surname'])) {
			throw new HttpException(400, 'names must not be empty');
		}

		$data['email'] = trim($paramFetcher->get('email'));
		if (empty($data['email']) || !$this->emailHelper->validEmail($data['email'])
			|| !$this->isEmailValidForRegistration($data['email'])) {
			throw new HttpException(400, 'email is not valid or already used');
		}

		$data['pw'] = trim($paramFetcher->get('password'));
		if (strlen($data['pw'] < self::MIN_PASSWORD_LENGTH)) {
			throw new HttpException(400, 'password is too short');
		}

		$data['gender'] = (int)$paramFetcher->get('gender');
		if ($data['gender'] > Gender::DIVERSE || $data['gender'] < Gender::NOT_SELECTED) {
			$data['gender'] = Gender::NOT_SELECTED;
		}

		$birthdate = Carbon::createFromFormat('Y-m-d', $paramFetcher->get('birthdate'));
		if (!$birthdate) {
			throw new HttpException(400, 'invalid birthdate');
		}
		$minBirthdate = Carbon::today()->subYears(self::MIN_AGE_YEARS);
		if ($birthdate > $minBirthdate) {
			throw new HttpException(400, 'you are not old enough');
		}

		$data['mobile_phone'] = strip_tags($paramFetcher->get('mobilePhone') ?? '');
		$data['newsletter'] = (int)$paramFetcher->get('subscribeNewsletter') == 1;

		try {
			// register user and send out registration email
			$id = $this->registerTransactions->registerUser($data);

			// return the created user
			$user = RestNormalization::normalizeUser($this->foodsaverGateway->getFoodsaverBasics($id));

			return $this->handleView($this->view($user, 200));
		} catch (\Exception $e) {
			throw new HttpException(500, 'could not register user');
		}
	}

	private function isEmailValidForRegistration(string $email): bool
	{
		return !$this->emailHelper->isFoodsharingEmailAddress($email)
			&& !$this->foodsaverGateway->emailExists($email);
	}

	/**
	 * @Rest\Delete("user/{userId}", requirements={"userId" = "\d+"})
	 */
	public function deleteUserAction(int $userId): Response
	{
		if (!$this->profilePermissions->mayDeleteUser($userId)) {
			throw new HttpException(403);
		}

		if ($userId === $this->session->id()) {
			$this->session->logout();
		}
		$this->foodsaverGateway->deleteFoodsaver($userId);

		return $this->handleView($this->view());
	}

	/**
	 * Gives a banana to a user.
	 *
	 * @SWG\Parameter(name="userId", in="path", type="integer", description="to which user to give the banana")
	 * @SWG\Parameter(name="message", in="body", type="string", description="message to the user")
	 * @SWG\Response(response="200", description="Success.")
	 * @SWG\Response(response="400", description="Accompanying message is too short.")
	 * @SWG\Response(response="403", description="Insufficient permissions to rate that user.")
	 * @SWG\Response(response="404", description="User to rate does not exist.")
	 * @SWG\Tag(name="user")
	 *
	 * @Rest\Put("user/{userId}/banana", requirements={"userId" = "\d+"})
	 * @Rest\RequestParam(name="message", nullable=false)
	 */
	public function addBanana(int $userId, ParamFetcher $paramFetcher): Response
	{
		// make sure that users may not give themselves bananas
		if (!$this->session->may() || $this->session->id() === $userId) {
			throw new HttpException(403);
		}

		// check if the user exists
		if (!$this->foodsaverGateway->foodsaverExists($userId)) {
			throw new HttpException(404);
		}

		// do not allow giving bananas twice
		if ($this->profileGateway->hasGivenBanana($this->session->id(), $userId)) {
			throw new HttpException(403);
		}

		// check length of message
		$message = trim($paramFetcher->get('message'));
		if (strlen($message) < self::MIN_RATING_MESSAGE_LENGTH) {
			throw new HttpException(400);
		}

		$this->profileGateway->giveBanana($userId, $message, $this->session->id());

		return $this->handleView($this->view([], 200));
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
