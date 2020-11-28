<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Mailbox\MailboxGateway;
use Foodsharing\Permissions\MailboxPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
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
	 * @OA\Parameter(name="emailId", in="path", @OA\Schema(type="integer"), description="which email to modify")
	 * @OA\Parameter(name="status", in="path", @OA\Schema(type="integer"), description="either 0 for unread or 1 for read")
	 * @OA\Response(response="200", description="Success.")
	 * @OA\Response(response="400", description="Invalid status.")
	 * @OA\Response(response="403", description="Insufficient permissions to modify the email.")
	 * @OA\Response(response="404", description="Email does not exist.")
	 * @OA\Tag(name="emails")
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
