<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityUpdateFoodsharepoint
{
	public string $type = 'foodsharepoint';
	public string $time; // public DateTime $time;
	public int $time_ts; // Legacy timestamp (still used for comparison)

	public string $desc;

	public int $fs_id;
	public string $fs_name;
	public int $region_id;
	public string $icon;

	public string $source;
	public string $title;
	public ?array $gallery;

	// Individual update-type properties
	public int $fsp_id;

	public static function create(
		string $time, // DateTime $time,
		int $time_ts,
		string $desc,
		int $fs_id,
		string $fs_name,
		int $region_id,
		string $icon,
		string $source,
		?array $gallery,
		int $fsp_id,
		string $fsp_name
	): self {
		$u = new self();

		$u->time = $time;
		$u->time_ts = $time_ts;

		$u->desc = $desc;

		$u->fs_id = $fs_id;
		$u->fs_name = $fs_name;
		$u->region_id = $region_id;
		$u->icon = $icon;

		$u->source = $source;
		$u->title = $fsp_name;
		$u->gallery = $gallery ?? [];

		$u->fsp_id = $fsp_id;

		return $u;
	}
}
