<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Permissions\MailboxPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MailboxRestController extends AbstractFOSRestController
{
	private MailboxGateway $mailboxGateway;
	private MailboxPermissions $mailboxPermissions;
	private Session $session;

	public function __construct(
		MailboxGateway $mailboxGateway,
		MailboxPermissions $mailboxPermissions,
		Session $session
	) {
		$this->mailboxGateway = $mailboxGateway;
		$this->mailboxPermissions = $mailboxPermissions;
		$this->session = $session;
	}

	/**
	 * Changes the unread status of an email. This does not care about the previous status, i.e. setting a
	 * read email to read will still result in a 'success' response.
	 *
	 * @SWG\Parameter(name="emailId", in="path", type="integer", description="which email to modify")
	 * @SWG\Parameter(name="status", in="path", type="integer", description="either 0 for unread or 1 for read")
	 * @SWG\Response(response="200", description="Success.")
	 * @SWG\Response(response="400", description="Invalid status.")
	 * @SWG\Response(response="403", description="Insufficient permissions to modify the email.")
	 * @SWG\Response(response="404", description="Email does not exist.")
	 * @SWG\Tag(name="emails")
	 *
	 * @Rest\Patch("emails/{emailId}/{status}", requirements={"emailId" = "\d+", "status" = "[0-1]"})
	 */
	public function setEmailStatusAction(int $emailId, int $status): Response
	{
		if (!$this->session->id() || !$this->mailboxPermissions->mayMessage($emailId)) {
			throw new HttpException(403);
		}

		$this->mailboxGateway->setRead($emailId, $status);

		return $this->handleView($this->view([], 200));
	}
}
