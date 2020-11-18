<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityUpdateEvent
{
	public string $type = 'event';
	public string $time;
	public int $time_ts; // Legacy timestamp (still used for comparison)

	public string $desc;
	public ?string $quickreply;
	public string $icon;
	public string $title;
	public string $source;
	public ?array $gallery;

	public int $fs_id;
	public string $fs_name;
	public int $entity_id;

	public static function create(
		string $time, // DateTime $time,
		int $time_ts,
		string $desc,
		?string $quickreply,
		int $fs_id,
		string $fs_name,
		string $icon,
		string $source,
		?array $gallery,
		int $event_id,
		string $event_name
	): self {
		$u = new self();

		$u->time = $time;
		$u->time_ts = $time_ts;

		$u->desc = $desc;
		$u->quickreply = $quickreply;
		$u->icon = $icon;
		$u->title = $event_name;
		$u->source = $source;
		$u->gallery = $gallery ?? [];

		$u->fs_id = $fs_id;
		$u->fs_name = $fs_name;
		$u->entity_id = $event_id;

		return $u;
	}
}
