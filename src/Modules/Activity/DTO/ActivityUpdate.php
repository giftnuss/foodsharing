<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityUpdate
{
	public string $type;
	public string $time;
	public int $time_ts; // Legacy timestamp (still used for comparison)

	public string $title;
	public string $desc;
	public string $source;
	public string $source_suffix;

	public string $icon;
	public array $gallery;
	public ?string $quickreply;

	public int $fs_id;
	public string $fs_name;
	public int $entity_id;
	public ?int $region_id;

	// Individual properties for forum updates
	public ?int $forum_post;
	public ?string $forum_type;

	public static function create(
		string $type,
		string $time,
		int $time_ts,
		string $title,
		string $desc,
		string $source,
		?string $source_suffix,
		string $icon,
		?array $gallery,
		?string $quickreply,
		int $fs_id,
		string $fs_name,
		int $entity_id,
		?int $region_id = null,
		?int $forum_post = null,
		?string $forum_type = null
	): self {
		$u = new self();

		$u->type = $type;
		$u->time = $time;
		$u->time_ts = $time_ts;

		$u->title = $title;
		$u->desc = $desc;
		$u->source = $source;
		$u->source_suffix = $source_suffix ?? '';

		$u->icon = $icon;
		$u->gallery = $gallery ?? [];
		$u->quickreply = $quickreply;

		$u->fs_id = $fs_id;
		$u->fs_name = $fs_name;
		$u->entity_id = $entity_id;
		$u->region_id = $region_id;

		$u->forum_post = $forum_post;
		$u->forum_type = $forum_type;

		return $u;
	}
}
