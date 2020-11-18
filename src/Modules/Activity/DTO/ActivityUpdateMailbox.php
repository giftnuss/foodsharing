<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityUpdateMailbox
{
	public string $time;
	public int $time_ts; // Legacy timestamp (still used for comparison)

	public string $type = 'mailbox';
	public string $desc;
	public string $title;

	public string $icon = '/img/mailbox-50x50.png';
	public string $source;
	public ?string $quickreply;

	public int $entity_id;

	// Individual update-type properties
	public string $sender_email;

	public static function create(
		string $time, // DateTime $time,
		int $time_ts,
		string $desc,
		?string $quickreply,
		string $mailbox_name,
		int $mailbox_id,
		string $subject,
		string $sender_email
	): self {
		$u = new self();

		$u->time = $time;
		$u->time_ts = $time_ts;

		$u->desc = $desc;
		$u->title = $subject;

		$u->source = $mailbox_name;
		$u->quickreply = $quickreply;

		$u->entity_id = $mailbox_id;

		$u->sender_email = $sender_email;

		return $u;
	}
}
