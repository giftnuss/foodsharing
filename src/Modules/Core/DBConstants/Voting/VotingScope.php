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
	 * All users from {@link Role::FOODSAVER} upwards) with and without verification.
	 */
	public const FOODSAVERS = 0;
	/**
	 * All users from {@link Role::FOODSAVER} upwards, only with verification.
	 */
	public const VERIFIED_FOODSAVERS = 1;
	/**
	 * All store managers from {@link Role::STORE_MANAGER} upwards that are currently active manager of at least one
	 * store in the poll's region.
	 */
	public const STORE_MANAGERS = 2;
	/**
	 * All ambassadors of the poll's region.
	 */
	public const AMBASSADORS = 3;

	public const VERIFIED_FOODSAVERS_HOME_DISTRICT = 4;

	public static function isValidScope(int $scope): bool
	{
		return in_array($scope, range(self::FOODSAVERS, self::VERIFIED_FOODSAVERS_HOME_DISTRICT));
	}
}
