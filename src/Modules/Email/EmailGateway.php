<?php

namespace Foodsharing\Modules\Email;

use Foodsharing\Modules\Core\BaseGateway;

class EmailGateway extends BaseGateway
{
	public function setEmailStatus($mail_id, $foodsaver, $status)
	{
		$query = '';
		if (is_array($foodsaver)) {
			$query = array();
			foreach ($foodsaver as $fs) {
				$query[] = '`foodsaver_id` = ' . (int)$fs['id'];
			}

			$query = implode(' OR ', $query);
		} else {
			$query = '`foodsaver_id` = ' . (int)$foodsaver;
		}

		$this->db->execute('
			UPDATE 	`fs_email_status`
			SET 	`status` = ' . (int)$status . '
			WHERE 	`email_id` = ' . (int)$mail_id . '
			AND 	(' . $query . ')
		');
	}

	public function getMailsLeft($mail_id)
	{
		return $this->db->fetchValue('SELECT COUNT(`email_id`) FROM `fs_email_status` WHERE `email_id` = ' . (int)$mail_id . ' AND `status` = 0');
	}

	public function getMailNext($mail_id)
	{
		return $this->db->fetchAll('
			SELECT
			s.`email_id`,
			fs.`id`,
			s.`status`,
			fs.`name`,
			fs.`geschlecht`,
			fs.`email`,
			fs.`token`

			FROM 		`fs_email_status` s,
						`fs_foodsaver` fs

			WHERE 		fs.`id` = s.`foodsaver_id`
			AND 		s.email_id = ' . (int)$mail_id . '

			AND 		s.`status` = 0

			LIMIT 10
		');
	}

	public function getOne_send_email($id)
	{
		$out = $this->db->fetch('
			SELECT
			`id`,
			`foodsaver_id`,
			`mailbox_id`,
			`complete`,
			`name`,
			`message`,
			`zeit`,
			`recip`,
			`attach`

			FROM 		`fs_send_email`

			WHERE 		`id` = ' . (int)$id);

		return $out;
	}

	public function initEmail($fs_id, $mailbox_id, $foodsaver, $message, $subject, $attach)
	{
		if ((int)$mailbox_id == 0) {
			throw new \Exception('mailbox_id is 0');
		}

		$attach_db = '';
		if ($attach !== false) {
			$attach_db = json_encode(array($attach));
		}

		$email_id = $this->db->insert('fs_send_email', [
			'foodsaver_id' => $fs_id,
			'mailbox_id' => $mailbox_id,
			'name' => $subject,
			'message' => $message,
			'zeit' => $this->db->now(),
			'attach' => $attach_db
		]);

		$query = array();
		foreach ($foodsaver as $fs) {
			$query[] = '(' . (int)$email_id . ',' . (int)$fs['id'] . ',0)';
		}

		/*
		 * Array
		(
			[0] => (33,56,0)
			[1] => (33,146,0)
		)
		 */
		$this->db->execute('
			INSERT INTO `fs_email_status` (`email_id`,`foodsaver_id`,`status`)
			VALUES
			' . implode(',', $query) . ';
		');
	}

	public function getSendMails($fs_id)
	{
		return $this->db->fetchAll('
			SELECT 	`name`,
					`message`,
					`zeit`
			FROM 	`fs_send_email`
			WHERE 	`foodsaver_id` = ' . (int)$fs_id . '
		');
	}

	public function getEmailsToSend($fs_id)
	{
		$row = $this->db->fetch('

				SELECT 	`fs_send_email`.`id`,
						`fs_send_email`.`name`,
						`fs_send_email`.`message`,
						`fs_send_email`.`zeit`,
						COUNT( `fs_email_status`.`foodsaver_id` ) AS `anz`

				FROM 	 `fs_send_email`,
						 `fs_email_status`

				WHERE 	`fs_email_status`.`email_id` =  `fs_send_email`.`id`

				AND 	`fs_send_email`.`foodsaver_id` = ' . (int)$fs_id . '

				AND 	`fs_email_status`.`status` = 0

			');

		if ($row['anz'] == 0) {
			return false;
		}

		return $row;
	}
}
