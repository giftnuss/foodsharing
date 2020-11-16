<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityUpdateForum
{
	public string $time; // public DateTime $time;
	public int $time_ts; // Legacy timestamp (still used for comparison)

	public string $desc;
	public ?string $quickreply;

	public int $fs_id;
	public string $fs_name;
	public string $icon;

	public string $source;
	public int $region_id;

	// Individual update-type properties
	public int $forum_thread;
	public int $forum_post;
	public string $forum_name;
	public string $forum_type;
	public ?string $is_bot;

	public static function create(
		string $time, // DateTime $time,
		int $time_ts,
		string $desc,
		string $quickreply,
		int $fs_id,
		string $fs_name,
		string $icon,
		string $source,
		int $region_id,
		int $forum_thread,
		int $forum_post,
		string $forum_name,
		string $forum_type,
		bool $is_bot
	): self {
		$u = new self();

		$u->time = $time;
		$u->time_ts = $time_ts;

		$u->desc = $desc;
		$u->quickreply = $quickreply;

		$u->fs_id = $fs_id;
		$u->fs_name = $fs_name;
		$u->icon = $icon;

		$u->source = $source;
		$u->region_id = $region_id;

		$u->forum_thread = $forum_thread;
		$u->forum_post = $forum_post;
		$u->forum_name = $forum_name;
		$u->forum_type = $forum_type;
		$u->is_bot = $is_bot ? '_bot' : null;

		return $u;
	}
}
