<?php

namespace Foodsharing\Modules\Activity\DTO;

class ActivityUpdateMailbox
{
	public string $time;
	public int $time_ts; // Legacy timestamp (still used for comparison)

	public string $desc;
	public ?string $quickreply;

	public int $mailbox_id;
	public string $mailbox_name;
	public string $icon = '/img/mailbox-50x50.png';

	// Individual update-type properties
	public string $subject;
	public string $sender_email;

	public static function create(
		string $time, // DateTime $time,
		int $time_ts,
		string $desc,
		?string $quickreply,
		int $mailbox_id,
		string $mailbox_name,
		string $subject,
		string $sender_email
	): self {
		$u = new self();

		$u->time = $time;
		$u->time_ts = $time_ts;

		$u->desc = $desc;
		$u->quickreply = $quickreply;

		$u->mailbox_id = $mailbox_id;
		$u->mailbox_name = $mailbox_name;

		$u->subject = $subject;
		$u->sender_email = $sender_email;

		return $u;
	}
}
