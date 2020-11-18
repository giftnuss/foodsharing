<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityUpdateBuddy
{
	public string $type = 'friendWall';
	public string $time;
	public int $time_ts; // Legacy timestamp (still used for comparison)

	public string $desc;

	public int $fs_id;
	public string $fs_name;
	public string $icon;

	public string $source;
	public string $source_suffix;
	public ?array $gallery;

	public static function create(
		string $time, // DateTime $time,
		int $time_ts,
		string $desc,
		int $fs_id,
		string $fs_name,
		string $icon,
		?array $gallery,
		bool $is_own
	): self {
		$u = new self();

		$u->time = $time;
		$u->time_ts = $time_ts;

		$u->desc = $desc;

		$u->fs_id = $fs_id;
		$u->fs_name = $fs_name;
		$u->icon = $icon;

		$u->source = $fs_name;
		$u->source_suffix = $is_own ? '_own' : '';
		$u->gallery = $gallery ?? [];

		return $u;
	}
}
