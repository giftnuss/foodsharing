<?php

// table fs_foodsaver

namespace Foodsharing\Modules\Core\DBConstants\Quiz;

/**
 * only valid for working groups
 * TINYINT(1) | NOT NULL DEFAULT '0'.
 */
class RoleType
{
	public const FOODSHARER = 0;
	public const FOODSAVER = 1;
	public const STORE_COORDINATOR = 2;
	public const AMBASSADOR = 3;
	public const ORGA = 4;
	public const ADMIN = 5;
}
