<?php

namespace Foodsharing\Controller;

use Foodsharing\Helpers\EmailHelper;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Email\EmailGateway;
use Foodsharing\Permissions\NewsletterEmailPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Rest controller for newsletter functions.
 */
final class NewsletterRestController extends AbstractFOSRestController
{
	private $emailGateway;
	private $newsletterEmailPermissions;
	private $session;
	private $emailHelper;

	private const NOT_ALLOWED = 'not allowed';
	private const INVALID_ADDRESS = 'invalid address';

	public function __construct(
		EmailGateway $emailGateway,
		NewsletterEmailPermissions $newsletterEmailPermissions,
		Session $session,
		EmailHelper $emailHelper
	) {
		$this->emailGateway = $emailGateway;
		$this->newsletterEmailPermissions = $newsletterEmailPermissions;
		$this->session = $session;
		$this->emailHelper = $emailHelper;
	}

	/**
	 * Sends a test newsletter email to the given address. Returns 200 on success, 401 if the current user may not
	 * send newsletters, or 500 if the email address is invalid.
	 *
	 * @Rest\Post("newsletter/test")
	 * @Rest\RequestParam(name="address")
	 * @Rest\RequestParam(name="subject")
	 * @Rest\RequestParam(name="message")
	 */
	public function sendTestEmailAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->newsletterEmailPermissions->mayAdministrateNewsletterEmail()) {
			throw new HttpException(401, self::NOT_ALLOWED);
		}

		$address = $paramFetcher->get('address');
		if (!$this->emailHelper->validEmail($address)) {
			throw new HttpException(500, self::INVALID_ADDRESS);
		}

		$this->emailHelper->libmail(false, $address, $paramFetcher->get('subject'), $paramFetcher->get('message'));

		return $this->handleView($this->view([], 200));
	}
}
