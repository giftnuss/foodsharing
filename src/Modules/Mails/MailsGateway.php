<?php

namespace Foodsharing\Modules\Mails;

use Foodsharing\Modules\Core\BaseGateway;

class MailsGateway extends BaseGateway
{
	public function saveMessage(
		$mailbox_id, // mailbox id
		$folder, // folder
		$from, // sender
		$to, // to
		$subject, // subject
		$body,
		$html,
		$time, // time,
		$attach = '', // attachements
		$read = 0,
		$answer = 0)
	{
		return $this->db->insert('fs_mailbox_message', [
			'mailbox_id' => $mailbox_id,
			'folder' => $folder,
			'sender' => strip_tags($from),
			'to' => strip_tags($to),
			'subject' => strip_tags($subject),
			'body' => strip_tags($body),
			'body_html' => $html,
			'time' => $time,
			'attach' => $attach,
			'read' => $read,
			'answer' => $answer
		]);
	}

	public function getMailboxIds($mb_names)
	{
		return $this->db->fetchAllValues('
			SELECT id FROM fs_mailbox WHERE `name` IN (' . $this->db->generatePlaceholders(count($mb_names)) . ')',
			$mb_names);
	}

	public function addBounceForMail($email, $category, \DateTime $time)
	{
		$this->db->insert('fs_email_bounces', [
			'email' => $email,
			'bounce_category' => $category,
			'bounced_at' => $time->format('Y-m-d H:i:s')
		]);
	}

	public function emailIsBouncing($email)
	{
		$softBounceCount = 0;
		$bounces = $this->db->fetchAllByCriteria('fs_email_bounces', [
			'bounce_category',
			'bounced_at'
		], [
			'email' => $email
			]
		);
		foreach ($bounces as $bounce) {
			$bounce_time = new \DateTime($bounce['bounced_at']);
			if (in_array($bounce['bounce_category'], ['full', 'autoreply', 'outofoffice', 'internal_error'])) {
				if ($bounce_time > new \DateTime('-1 month')) {
					++$softBounceCount;
					if ($softBounceCount >= 2) {
						return true;
					}
				}
			} else {
				return true;
			}
		}

		return false;
	}
}
