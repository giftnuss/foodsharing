<?php

namespace Foodsharing\Modules\Core\DBConstants\Voting;

/**
 * Table `fs_poll`, column `scope`.
 * TINYINT(2) UNSIGNED NOT NULL
 */
class VotingScope
{
	public const ALL_USERS = 0;
	public const FOODSAVERS = 1;
	public const VERIFIED_FOODSAVERS = 2;
	public const STORE_MANAGERS = 3;
	public const AMBASSADORS = 4;
}
