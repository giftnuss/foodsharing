<?php

namespace Foodsharing\Modules\Mailbox;

use Foodsharing\Modules\Core\BaseGateway;

class MailboxGateway extends BaseGateway
{
	public function getMailboxname($mailbox_id)
	{
		try {
			return $this->db->fetchValue('SELECT `name` FROM fs_mailbox WHERE id = ' . (int)$mailbox_id);
		} catch (\Exception $e) {
			// trigger_error('No mailbox found with id ' . $mailbox_id);
			return false;
		}
	}
}
