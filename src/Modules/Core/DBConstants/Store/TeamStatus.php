<?php

// table `fs_betrieb`

namespace Foodsharing\Modules\Core\DBConstants\Store;

/**
 * column `team_status`
 * store team states
 * TINYINT(2)          NOT NULL DEFAULT '1',.
 */
class TeamStatus
{
	public const CLOSED = 0;
	public const OPEN = 1;
	public const OPEN_SEARCHING = 2;
}
