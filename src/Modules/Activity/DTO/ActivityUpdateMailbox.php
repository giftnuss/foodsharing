<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityUpdateMailbox
{
	public string $type = 'mailbox';
	public string $time;
	public int $time_ts; // Legacy timestamp (still used for comparison)

	public string $desc;
	public ?string $quickreply;

	public string $icon = '/img/mailbox-50x50.png';

	public string $source;
	public string $title;

	// Individual update-type properties
	public int $mailbox_id;
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
		$u->quickreply = $quickreply;

		$u->source = $mailbox_name;
		$u->title = $subject;

		$u->mailbox_id = $mailbox_id;
		$u->sender_email = $sender_email;

		return $u;
	}
}
