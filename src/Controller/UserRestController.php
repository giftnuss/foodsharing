<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Login\LoginGateway;
use Foodsharing\Services\SearchService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Mobile_Detect;

class UserRestController extends FOSRestController
{
	private $session;
	private $loginGateway;
	private $searchService;

	public function __construct(Session $session, LoginGateway $loginGateway, SearchService $searchService)
	{
		$this->session = $session;
		$this->loginGateway = $loginGateway;
		$this->searchService = $searchService;
	}

	/**
	 * @Rest\Post("user/login")
	 * @Rest\RequestParam(name="email")
	 * @Rest\RequestParam(name="password")
	 */
	public function loginAction(ParamFetcher $paramFetcher)
	{
		$email = $paramFetcher->get('email');
		$password = $paramFetcher->get('password');
		$fs_id = $this->loginGateway->login($email, $password);
		if ($fs_id) {
			$this->session->login($fs_id);

			$token = $this->searchService->writeSearchIndexToDisk($this->session->id(), $this->session->user('token'));

			$mobdet = new Mobile_Detect();
			if ($mobdet->isMobile()) {
				$_SESSION['mob'] = 1;
			}
			$user = $this->session->get('user');

			return $this->handleView($this->view([
				'id' => $fs_id,
				'name' => $user['name']
				// this response can get extended further, once needed
			], 200));
		}

		throw new HttpException(401, 'email or password are invalid');
	}
}
