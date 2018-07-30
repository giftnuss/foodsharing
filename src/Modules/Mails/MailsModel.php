<?php

namespace Foodsharing\Modules\Mails;

use Foodsharing\Lib\Db\Db;

class MailsModel extends Db
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
		return $this->insert('
			INSERT INTO `fs_mailbox_message`
			(
				`mailbox_id`,
				`folder`,
				`sender`,
				`to`,
				`subject`,
				`body`,
				`body_html`,
				`time`,
				`attach`,
				`read`,
				`answer`
			)
			VALUES
			(
				' . (int)$mailbox_id . ',
				' . (int)$folder . ',
				' . $this->strval($from) . ',
				' . $this->strval($to) . ',
				' . $this->strval($subject) . ',
				' . $this->strval($body) . ',
				' . $this->strval($html, true) . ',
				' . $this->strval($time) . ',
				' . $this->strval($attach) . ',
				' . (int)$read . ',
				' . (int)$answer . '
			)
		');
	}

	public function getMailboxId($mb_name)
	{
		return $this->qOne('
			SELECT id FROM fs_mailbox WHERE `name` = ' . $this->strval($mb_name) . '
		');
	}

	public function getMailboxIds($mb_names)
	{
		$where = array();
		foreach ($mb_names as $n) {
			$where[] = $this->strval($n);
		}

		return $this->qCol('
			SELECT id FROM fs_mailbox WHERE `name` IN(' . implode(',', $where) . ')
		');
	}
}
