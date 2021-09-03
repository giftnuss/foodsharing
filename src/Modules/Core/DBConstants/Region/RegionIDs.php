<?php

namespace Foodsharing\Modules\Core\DBConstants\Region;

class RegionIDs
{
	// upper level holding groups
	public const ROOT = 0;
	public const GLOBAL_WORKING_GROUPS = 392;
	public const EUROPE_WELCOME_TEAM = 813;
	public const EUROPE = 741; // second level from top. First selectable level

	// workgroups with special permissions:
	public const NEWSLETTER_WORK_GROUP = 331;
	public const QUIZ_AND_REGISTRATION_WORK_GROUP = 341;
	public const PR_PARTNER_AND_TEAM_WORK_GROUP = 1811;
	public const PR_START_PAGE = 2287;
	public const EUROPE_REPORT_TEAM = 432;
	public const CREATING_WORK_GROUPS_WORK_GROUP = 1701;
	public const IT_SUPPORT_GROUP = 387;
	public const EDITORIAL_GROUP = 327;

	// region and ambassador groups
	public const EUROPE_BOT_GROUP = 881;
	public const AUSTRIA = 63;
	public const AUSTRIA_BOT_GROUP = 761;
	public const SWITZERLAND = 106;
	public const SWITZERLAND_BOT_GROUP = 1763;
	public const VOTING_ADMIN_GROUP = 3871;
	public const ORGA_COORDINATION_GROUP = 3818;

	// groups used for displaying team page:
	public const TEAM_BOARD_MEMBER = 1373;
	public const TEAM_ALUMNI_MEMBER = 1564;
	public const TEAM_ADMINISTRATION_MEMBER = 1565;
	public const WORKGROUP_ADMIN_CREATION_GROUP = 1701;

	// Testregions
	public const TESTREGION_MASTER = 260;
	public const TESTREGION_1 = 343;
	public const TESTREGION_2 = 3113;

	public static function hasSpecialPermission(int $regionId): bool
	{
		return in_array($regionId, [
			self::NEWSLETTER_WORK_GROUP, self::QUIZ_AND_REGISTRATION_WORK_GROUP,
			self::PR_PARTNER_AND_TEAM_WORK_GROUP, self::PR_START_PAGE,
			self::EUROPE_REPORT_TEAM, self::IT_SUPPORT_GROUP,
			self::EDITORIAL_GROUP
		]);
	}

	public static function getTestRegions(): array
	{
		return [
			self::TESTREGION_MASTER,
			self::TESTREGION_1,
			self::TESTREGION_2
		];
	}
}
