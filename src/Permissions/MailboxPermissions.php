<?php

namespace Foodsharing\Permissions;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Mailbox\MailboxGateway;

class MailboxPermissions
{
	private Session $session;
	private MailboxGateway $mailboxGateway;

	public function __construct(MailboxGateway $mailboxGateway, Session $session)
	{
		$this->mailboxGateway = $mailboxGateway;
		$this->session = $session;
	}

	public function mayMessage(int $mid): bool
	{
		if (!$this->mayHaveMailbox()) {
			return false;
		}

		if ($mailbox_id = $this->mailboxGateway->getMailboxId($mid)) {
			return $this->mayMailbox($mailbox_id);
		}

		return false;
	}

	public function mayMailbox(int $mailboxId): bool
	{
		$boxes = $this->mailboxGateway->getBoxes($this->session->isAmbassador(), $this->session->id(), $this->session->may('bieb'));

		foreach ($boxes as $b) {
			if ($b['id'] == $mailboxId) {
				return true;
			}
		}

		return false;
	}

	public function mayManageMailboxes(): bool
	{
		return $this->session->may('orga');
	}

	public function mayAddMailboxes(): bool
	{
		return $this->mayManageMailboxes();
	}

	public function mayHaveMailbox(): bool
	{
		return $this->session->may('bieb');
	}
}
