<?php

namespace Foodsharing\Modules\Core\DBConstants\Email;

/**
 * Status of emails. Column 'status' in table 'fs_email_status'.
 * TINYINT(3) UNSIGNED.
 */
class EmailStatus
{
	public const STATUS_INITIALISED = 1;
	public const STATUS_SENT = 2;
	public const STATUS_INVALID_MAIL = 3;
	public const STATUS_CANCELED = 4;
}
