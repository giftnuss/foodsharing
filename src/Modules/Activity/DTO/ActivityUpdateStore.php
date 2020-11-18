<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityUpdateStore
{
	public string $time; // public DateTime $time;
	public int $time_ts; // Legacy timestamp (still used for comparison)

	public string $type = 'store';
	public string $desc;
	public string $title;

	public string $icon;
	public string $source;

	public int $fs_id;
	public string $fs_name;
	public int $entity_id;

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
		$u->title = $store_name;

		$u->icon = $icon;
		$u->source = $region_name;

		$u->fs_id = $fs_id;
		$u->fs_name = $fs_name;
		$u->entity_id = $store_id;

		return $u;
	}
}
