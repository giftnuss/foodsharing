<?php

// table fs_buddy

namespace Foodsharing\Modules\Core\DBConstants\Buddy;

/**
 * column `buddy_id`
 * IDs for buddy request states
 * INT(10) UNSIGNED NOT NULL.
 */
class BuddyId
{
	public const NO_BUDDY = -1;
	public const REQUESTED = 0;
	public const BUDDY = 1;
}
