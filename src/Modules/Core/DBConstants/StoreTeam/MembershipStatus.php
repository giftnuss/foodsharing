<?php

// table `fs_betrieb_team`

namespace Foodsharing\Modules\Core\DBConstants\StoreTeam;

/**
 * column `active`
 * membership states for foodsavers and foodsharers
 * INT(11)          NOT NULL DEFAULT '0',.
 */
class MembershipStatus
{
	public const APPLIED_FOR_TEAM = 0;
	public const MEMBER = 1;
	public const JUMPER = 2;
}
