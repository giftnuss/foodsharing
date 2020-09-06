<?php

// table `fs_betrieb_notiz`

namespace Foodsharing\Modules\Core\DBConstants\Store;

class Milestone
{
	public const NONE = 0; // regular user wallpost / comment
	public const CREATED = 1;
	public const ACCEPTED = 2;
	public const STATUS_CHANGED = 3; // this is no longer generated
	// ancient or unused = 4;
	public const DROPPED = 5; // this is no longer generated

	public static function isStoreMilestone(int $value): bool
	{
		return in_array($value, [
			self::CREATED,
			self::STATUS_CHANGED,
		]);
	}

	public static function isTeamMilestone(int $value): bool
	{
		return in_array($value, [
			self::ACCEPTED,
			self::DROPPED,
		]);
	}
}
