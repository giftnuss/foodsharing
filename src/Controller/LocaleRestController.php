<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session as Session;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;

class LocaleRestController extends AbstractFOSRestController
{
	/**
	 * Returns the locale setting for the current session.
	 *
	 * @Rest\Get("locale")
	 */
	public function getLocaleAction(Session $session): Response
	{
		$locale = $session->getLocale();

		return $this->handleView($this->view(['locale' => $locale], 200));
	}

	/**
	 * Sets the locale for the current session.
	 *
	 * @Rest\Post("locale")
	 * @Rest\RequestParam(name="locale")
	 */
	public function setLocaleAction(ParamFetcher $paramFetcher, Session $session): Response
	{
		$session->set('locale', $paramFetcher->get('locale'));

		return $this->getLocaleAction($session);
	}
}
