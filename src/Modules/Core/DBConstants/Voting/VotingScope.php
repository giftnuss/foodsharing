<?php

namespace Foodsharing\Modules\Core\DBConstants\Voting;

/**
 * Scope of a poll that determines who is allowed to vote.
 *
 * Table `fs_poll`, column `scope`. TINYINT(2) UNSIGNED NOT NULL.
 */
class VotingScope
{
	/**
	 * All users including foodsharers.
	 */
	public const ALL_USERS = 0;
	/**
	 * All users from {@link Role::FOODSAVER} upwards) with and without verification.
	 */
	public const FOODSAVERS = 1;
	/**
	 * All users from {@link Role::FOODSAVER} upwards, only with verification.
	 */
	public const VERIFIED_FOODSAVERS = 2;
	/**
	 * All store managers from {@link Role::STORE_MANAGER} upwards that are currently active manager of at least one
	 * store in the poll's region.
	 */
	public const STORE_MANAGERS = 3;
	/**
	 * All ambassadors of the poll's region.
	 */
	public const AMBASSADORS = 4;

	public static function isValidScope(int $scope): bool
	{
		return in_array($scope, range(self::ALL_USERS, self::AMBASSADORS));
	}
}
