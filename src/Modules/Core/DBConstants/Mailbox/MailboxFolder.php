<?php

namespace Foodsharing\Modules\Core\DBConstants\Mailbox;

/**
 * IDs for mailbox folders. Column `folder` in table `fs_mailbox_message`.
 */
class MailboxFolder
{
	public const FOLDER_INBOX = 1;
	public const FOLDER_SENT = 2;
	public const FOLDER_TRASH = 3;
}
