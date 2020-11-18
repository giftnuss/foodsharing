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
	public string $icon;

	public string $source;
	public ?array $gallery;

	// Individual update-type properties
	public int $fsp_id;
	public string $fsp_name;
	public string $region_name;

	public static function create(
		string $time, // DateTime $time,
		int $time_ts,
		string $desc,
		int $fs_id,
		string $fs_name,
		string $icon,
		string $source,
		?array $gallery,
		int $fsp_id,
		string $fsp_name,
		string $region_name
	): self {
		$u = new self();

		$u->time = $time;
		$u->time_ts = $time_ts;

		$u->desc = $desc;

		$u->fs_id = $fs_id;
		$u->fs_name = $fs_name;
		$u->icon = $icon;

		$u->source = $source;
		$u->gallery = $gallery ?? [];

		$u->fsp_id = $fsp_id;
		$u->fsp_name = $fsp_name;
		$u->region_name = $region_name;

		return $u;
	}
}
