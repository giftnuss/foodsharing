<?php

namespace Foodsharing\Modules\Core\DBConstants\Voting;

/**
 * Type of a poll that determines how and how many options can be chosen by voters.
 *
 * Table `fs_poll`, column `type`. TINYINT(1) UNSIGNED NOT NULL.
 */
class VotingType
{
	/**
	 * Users can select only one of multiple options by radio buttons.
	 */
	public const SELECT_ONE_CHOICE = 0;
	/**
	 * Users can select a variable number of options by checkboxes.
	 */
	public const SELECT_MULTIPLE = 1;
	/**
	 * Users can rate each option with a thumbs up, thumbs down, or neutral (+1, -1, 0).
	 */
	public const THUMB_VOTING = 2;
	/**
	 * Users can rate each option with a value from -3 to +3.
	 */
	public const SCORE_VOTING = 3;

	public static function isValidType(int $scope): bool
	{
		return in_array($scope, range(self::SELECT_ONE_CHOICE, self::SCORE_VOTING));
	}
}
