<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SearchRestController extends FOSRestController
{
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	/**
	 * @Rest\Get("search/legacyindex")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getSearchLegacyIndexAction()
	{
		if (!$this->session->id()) {
			throw new HttpException(403);
		}
		$file = 'cache/searchindex/' . $this->session->user('token') . '.json';
		if (!file_exists($file)) {
			$data = [];
		} else {
			$data = json_decode(file_get_contents($file), true);
		}

		// TODO: add caching header
		$view = $this->view($data, 200);

		return $this->handleView($view);
	}
}
