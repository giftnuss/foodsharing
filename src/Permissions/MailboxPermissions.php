<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Mailbox\MailboxGateway;

class MailboxPermissions
{
	private $session;
	private $mailboxGateway;

	public function __construct(MailboxGateway $mailboxGateway, Session $session)
	{
		$this->mailboxGateway = $mailboxGateway;
		$this->session = $session;
	}

	public function mayMessage(int $mid): bool
	{
		if ($mailbox_id = $this->mailboxGateway->getMailboxId($mid)) {
			return $this->mayMailbox($mailbox_id);
		}

		return false;
	}

	public function mayMailbox(int $mb_id): bool
	{
		if ($boxes = $this->mailboxGateway->getBoxes($this->session->isAmbassador(), $this->session->id(), $this->session->may('bieb'))) {
			foreach ($boxes as $b) {
				if ($b['id'] == $mb_id) {
					return true;
				}
			}
		}

		return false;
	}
}
