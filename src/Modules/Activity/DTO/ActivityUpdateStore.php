<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityUpdateStore
{
	public string $time; // public DateTime $time;
	public int $time_ts; // Legacy timestamp (still used for comparison)

	public string $desc;

	public int $fs_id;
	public string $fs_name;
	public string $icon;

	public string $source;

	// Individual update-type properties
	public int $store_id;
	public string $store_name;

	public static function create(
		string $time, // DateTime $time,
		int $time_ts,
		string $desc,
		int $fs_id,
		string $fs_name,
		string $icon,
		string $region_name,
		int $store_id,
		string $store_name
	): self {
		$u = new self();

		$u->time = $time;
		$u->time_ts = $time_ts;

		$u->desc = $desc;

		$u->fs_id = $fs_id;
		$u->fs_name = $fs_name;
		$u->icon = $icon;

		$u->source = $region_name;

		$u->store_id = $store_id;
		$u->store_name = $store_name;

		return $u;
	}
}
