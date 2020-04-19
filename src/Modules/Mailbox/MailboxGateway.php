<?php

namespace Foodsharing\Modules\Mailbox;

use Exception;
use Foodsharing\Modules\Core\BaseGateway;

class MailboxGateway extends BaseGateway
{
	public function getMailboxname(int $mailbox_id)
	{
		try {
			return $this->db->fetchValueByCriteria('fs_mailbox', 'name', ['id' => $mailbox_id]);
		} catch (\Exception $e) {
			// trigger_error('No mailbox found with id ' . $mailbox_id);
			return false;
		}
	}

	public function mailboxActivity(int $mid): int
	{
		return $this->db->update('fs_mailbox', ['last_access' => $this->db->now()], ['id' => $mid]);
	}

	public function addContact(string $email, int $fsId): bool
	{
		try {
			$id = $this->db->fetchValueByCriteria('fs_contact', 'id', ['email' => strip_tags($email)]);
		} catch (Exception $e) {
			$id = $this->db->insert('fs_contact', ['email' => $email]);
		}

		if ((int)$id > 0) {
			$this->db->insertIgnore('fs_foodsaver_has_contact', ['foodsaver_id' => $fsId, 'contact_id' => (int)$id]);

			return true;
		}

		return false;
	}

	public function getMailAdresses(int $fsId)
	{
		$mails = $this->db->fetchAllValues(
			'
			SELECT 	CONCAT(mb.name,"@' . PLATFORM_MAILBOX_HOST . '")
			FROM 	fs_mailbox mb,
					fs_bezirk bz
			WHERE 	bz.mailbox_id = mb.id
		'
		);

		if ($contacts = $this->db->fetchAllValues(
			'
			SELECT 	c.`email`
			FROM 	fs_contact c,
					fs_foodsaver_has_contact fc
			WHERE 	fc.contact_id = c.id
			AND 	fc.foodsaver_id = :fs_id
		',
			[':fs_id' => $fsId]
		)
		) {
			$mails = array_merge($mails, $contacts);
		}

		return $mails ? $mails : [];
	}

	public function addMailbox(string $name, int $member = 0): int
	{
		return $this->db->insert('fs_mailbox', ['name' => strip_tags($name), 'member' => $member]);
	}

	public function getNewCount(array $boxes): array
	{
		$barr = [];
		foreach ($boxes as $b) {
			$barr[] = $b['id'];
		}

		return $this->db->fetchAll(
			'
			SELECT 	COUNT(`mm`.id) AS count,
					mb.name,
					mb.id
			FROM 	`fs_mailbox` mb,
					`fs_mailbox_message` mm
			WHERE 	mm.mailbox_id = mb.id
			AND 	mb.id IN(' . implode(',', array_map('intval', $barr)) . ')
			AND 	mm.read = 0
			GROUP BY mm.mailbox_id
		'
		);
	}

	public function setAnswered(int $message_id): int
	{
		return $this->db->update('fs_mailbox_message', ['answer' => 1], ['id' => $message_id]);
	}

	public function deleteMessage(int $mid): int
	{
		$attach = $this->db->fetchValueByCriteria('fs_mailbox_message', 'attach', ['id' => $mid]);
		if (!empty($attach)) {
			$attach = json_decode($attach, true);
			if (is_array($attach)) {
				foreach ($attach as $a) {
					if (isset($a['filename'])) {
						@unlink('data/mailattach/' . $a['filename']);
					}
				}
			}
		}

		return $this->db->delete('fs_mailbox_message', ['id' => $mid]);
	}

	public function move(int $mail_id, int $folder): int
	{
		return $this->db->update('fs_mailbox_message', ['folder' => $folder], ['id' => $mail_id]);
	}

	public function getMessage(int $message_id)
	{
		return $this->db->fetch(
			'
			SELECT 	m.`id`,
					m.`folder`,
					m.`sender`,
					m.`to`,
					m.`subject`,
					m.`time`,
					UNIX_TIMESTAMP(m.`time`) AS time_ts,
					m.`attach`,
					m.`read`,
					m.`answer`,
					m.`body`,
					m.`mailbox_id`,
					b.name AS mailbox
			FROM 	fs_mailbox_message m
			LEFT JOIN fs_mailbox b
			ON m.mailbox_id = b.id
			WHERE	m.id = :message_id
		',
			[':message_id' => $message_id]
		);
	}

	public function setRead(int $mail_id, int $read): int
	{
		return $this->db->update('fs_mailbox_message', ['read' => $read], ['id' => $mail_id]);
	}

	public function listMessages(int $mailbox_id, int $folder): array
	{
		return $this->db->fetchAll(
			'
			SELECT 	`id`,
					`folder`,
					`sender`,
					`to`,
					`subject`,
					`time`,
					UNIX_TIMESTAMP(`time`) AS time_ts,
					`attach`,
					`read`,
					`answer`
			FROM 	fs_mailbox_message
			WHERE	mailbox_id = :mailbox_id
			AND 	folder = :farray_folder
			ORDER BY `time` DESC
		',
			[':mailbox_id' => $mailbox_id, ':farray_folder' => $folder]
		);
	}

	public function saveMessage(
		int $mailbox_id, // mailbox id
		int $folder, // folder
		string $from, // sender
		string $to, // to
		string $subject, // subject
		string $body,
		string $html,
		string $time, // time,
		string $attach = '', // attachements
		int $read = 0,
		int $answer = 0
	): int {
		return $this->db->insert(
			'fs_mailbox_message',
			[
				'mailbox_id' => $mailbox_id,
				'folder' => $folder,
				'sender' => strip_tags($from),
				'to' => strip_tags($to),
				'subject' => strip_tags($subject),
				'body' => strip_tags($body),
				'body_html' => strip_tags($html),
				'time' => strip_tags($time),
				'attach' => strip_tags($attach),
				'read' => $read,
				'answer' => $answer,
			]
		);
	}

	public function getMailbox(int $mb_id)
	{
		if ($mb = $this->db->fetchByCriteria('fs_mailbox', ['name'], ['id' => $mb_id])) {
			$mb['email_name'] = '';
			try {
				$mb['email_name'] = $this->db->fetchValue(
					'SELECT CONCAT(name," ", nachname) FROM fs_foodsaver WHERE mailbox_id = :mb_id',
					[':mb_id' => $mb_id]
				);

				return $mb;
			} catch (Exception $e) {
			}

			try {
				$mb['email_name'] = $this->db->fetchValueByCriteria(
					'fs_bezirk',
					'email_name',
					['mailbox_id' => $mb_id]
				);

				return $mb;
			} catch (Exception $e) {
			}

			try {
				$mb['email_name'] = $this->db->fetchValue(
					'SELECT email_name FROM fs_mailbox_member WHERE mailbox_id = :mb_id AND email_name != "" LIMIT 1',
					[':mb_id' => $mb_id]
				);

				return $mb;
			} catch (Exception $e) {
			}
		}

		return false;
	}

	public function getMemberBoxes()
	{
		if ($boxes = $this->db->fetchAllByCriteria('fs_mailbox', ['name', 'id'], ['member' => 1])
		) {
			foreach ($boxes as $key => $b) {
				$boxes[$key]['email_name'] = '';
				if ($boxes[$key]['member'] = $this->db->fetchAll(
					'
					SELECT 	fs.id AS id,
							CONCAT(fs.name," ",fs.nachname) AS name,
							mm.email_name
					FROM 	`fs_mailbox_member` mm,
							`fs_foodsaver` fs
					WHERE 	mm.foodsaver_id = fs.id
					AND 	mm.mailbox_id = :b_id
				',
					[':b_id' => (int)$b['id']]
				)
				) {
					foreach ($boxes[$key]['member'] as $mm) {
						if (!empty($mm['email_name'])) {
							$boxes[$key]['email_name'] = $mm['email_name'];
						}
					}
				}
			}

			return $boxes;
		}

		return false;
	}

	public function updateMember(int $mbid, array $foodsaver): bool
	{
		global $g_data;
		if ($mbid > 0) {
			$this->db->delete('fs_mailbox_member', ['mailbox_id' => $mbid]);

			$insert = [];

			foreach ($foodsaver as $fs) {
				$insert[] = [
					'mailbox_id' => $mbid,
					'foodsaver_id' => (int)$fs,
					'email_name' => '\'' . strip_tags($g_data['email_name']) . '\''
				];
			}

			$this->db->insertMultiple('fs_mailbox_member', $insert);

			return true;
		}

		return false;
	}

	public function filterName(string $mb_name)
	{
		$mb_name = strtolower($mb_name);
		$mb_name = trim($mb_name);
		$mb_name = str_replace(
			['ä', 'ö', 'ü', 'è', 'à', 'ß', ' ', '-', '/', '\\'],
			['ae', 'oe', 'ue', 'e', 'a', 'ss', '.', '.', '.', '.'],
			$mb_name
		);
		$mb_name = preg_replace('/[^0-9a-z\.]/', '', $mb_name);

		if (!empty($mb_name)) {
			return $mb_name;
		}

		return false;
	}

	/**
	 * Get region IDs from all member-groups and regions where the user is ambassador / admin.
	 */
	private function getMailboxAdminRegions(int $fsId): array
	{
		return $this->db->fetchAllValuesByCriteria('fs_botschafter', 'bezirk_id', ['foodsaver_id' => $fsId]);
	}

	public function getBoxes(bool $isAmbassador, int $fsId, bool $mayStoreManager)
	{
		if ($isAmbassador) {
			$mailboxAdminRegions = $this->getMailboxAdminRegions($fsId);
			$selectedRegions = [];
			$mBoxes = [];
			foreach ($mailboxAdminRegions as $region) {
				$selectedRegions[] = (int)$region;
			}

			if ($mailboxAdminRegions = $this->db->fetchAll(
				'
				SELECT 	`id`,`mailbox_id`,`name`
				FROM 	`fs_bezirk`
				WHERE 	`id` IN (' . implode(',', array_map('intval', $selectedRegions)) . ')
				AND 	`mailbox_id` = 0
			'
			)
			) {
				foreach ($mailboxAdminRegions as $region) {
					if ($region['mailbox_id'] == 0) {
						$mb_name = strtolower($region['name']);
						$mb_name = trim($mb_name);
						$mb_name = str_replace(
							['ä', 'ö', 'ü', 'è', 'à', 'ß', ' ', '-', '/', '\\'],
							['ae', 'oe', 'ue', 'e', 'a', 'ss', '.', '.', '.', '.'],
							$mb_name
						);
						$mb_name = preg_replace('/[^0-9a-z\.]/', '', $mb_name);

						if ($mb_name[0] !== '.' && strlen($mb_name) <= 3) {
							continue;
						}

						$mb_id = $this->createMailbox($mb_name);

						if ($this->db->update('fs_bezirk', ['mailbox_id' => (int)$mb_id], ['id' => (int)$region['id']])) {
							$region['mailbox_id'] = $mb_id;
						}
					}
				}
			}
			if ($mailboxAdminRegions = $this->db->fetchAll(
				'
				SELECT 	m.`id`,
						m.`name`,
						b.email_name,
						b.id AS bezirk_id
				FROM 	`fs_bezirk` b,
						`fs_mailbox` m
				WHERE 	b.mailbox_id = m.id
				AND 	b.`id` IN (' . implode(',', array_map('intval', $selectedRegions)) . ')
			'
			)
			) {
				foreach ($mailboxAdminRegions as $region) {
					if (empty($region['email_name'])) {
						$region['email_name'] = 'foodsharing ' . $region['name'];
						$this->db->update(
							'fs_bezirk',
							['email_name' => strip_tags($region['email_name'])],
							['id' => (int)$region['bezirk_id']]
						);
					}
					$mBoxes[] = [
						'id' => $region['id'],
						'name' => $region['name'],
						'email_name' => $region['email_name'],
						'type' => 'bot',
					];
				}
			}
		}

		$me = [];
		try {
			$me = $this->db->fetchByCriteria(
			'fs_foodsaver',
			['mailbox_id', 'name', 'nachname'],
			['id' => $fsId]
		);
		} catch (\Exception $e) {
			// until now it does nothing, if no value is found
		}
		if ($mayStoreManager && $me && $me['mailbox_id'] == 0) {
			$me['name'] = explode(' ', $me['name']);
			$me['name'] = $me['name'][0];

			$me['nachname'] = explode(' ', $me['nachname']);
			$me['nachname'] = $me['nachname'][0];

			$mb_name = strtolower(substr($me['name'], 0, 1) . '.' . $me['nachname']);
			$mb_name = trim($mb_name);
			$mb_name = str_replace(['ä', 'ö', 'ü', 'è', 'ß', ' '], ['ae', 'oe', 'ue', 'e', 'ss', '.'], $mb_name);
			$mb_name = preg_replace('/[^0-9a-z\.]/', '', $mb_name);

			$mb_name = substr($mb_name, 0, 25);

			if ($mb_name[0] !== '.' && strlen($mb_name) > 3) {
				$mb_id = $this->createMailbox($mb_name);
				if ($this->db->update('fs_foodsaver', ['mailbox_id' => (int)$mb_id], ['id' => $fsId])) {
					$me['mailbox_id'] = $mb_id;
				}
			}
		}
		if ($memberb = $this->db->fetchAll(
			'
			SELECT 	mb.`name`,
					mb.`id`,
					mm.email_name
			FROM	`fs_mailbox` mb,
					`fs_mailbox_member` mm
			WHERE 	mm.mailbox_id = mb.id
			AND 	mm.foodsaver_id = :fs_id
		',
			[':fs_id' => $fsId]
		)
		) {
			foreach ($memberb as $m) {
				if (empty($m['email_name'])) {
					$m['email_name'] = $m['name'] . '@' . PLATFORM_MAILBOX_HOST;
					$this->db->update(
						'fs_mailbox_member',
						['email_name' => strip_tags($m['name']) . '@' . PLATFORM_MAILBOX_HOST],
						['mailbox_id' => (int)$m['id'], 'foodsaver_id' => $fsId]
					);
				}
				$mBoxes[] = [
					'id' => $m['id'],
					'name' => $m['name'],
					'email_name' => $m['email_name'],
					'type' => 'member',
				];
			}
		}

		if ($mebox = $this->db->fetch(
			'
				SELECT 		m.`id`,
							m.name,
							CONCAT(fs.`name`," ",fs.`nachname`) AS email_name
				FROM 		`fs_mailbox` m,
							`fs_foodsaver` fs
				WHERE 		fs.mailbox_id = m.id
				AND 		fs.id = :fs_id
			',
			[':fs_id' => $fsId]
		)
		) {
			$mBoxes[] = [
				'id' => $mebox['id'],
				'name' => $mebox['name'],
				'email_name' => $mebox['email_name'],
				'type' => 'fs',
			];
		}

		return empty($mBoxes) ? false : $mBoxes;
	}

	public function getMailboxId(int $mid)
	{
		try {
			return $this->db->fetchValueByCriteria('fs_mailbox_message', 'mailbox_id', ['id' => $mid]);
		} catch (Exception $e) {
			return 0;
		}
	}

	/**
	 * Returns the mailbox ID and attachment info for the message ID. The attachment info is a json encoded list that
	 * contains 'filename', 'origname', and 'mime' for each attachment.
	 *
	 * @return array
	 */
	public function getAttachmentFileInfo(int $messageId)
	{
		return $this->db->fetchByCriteria('fs_mailbox_message', ['mailbox_id', 'attach'], ['id' => $messageId]);
	}

	/**
	 * Returns the folder of the mail with this message ID.
	 */
	public function getMailFolderId(int $messageId): int
	{
		return $this->db->fetchValueByCriteria('fs_mailbox_message', 'folder', ['id' => $messageId]);
	}

	/**
	 * Returns the HTML body of the mail with this message ID.
	 */
	public function getMessageHtmlBody(int $messageId): string
	{
		return $this->db->fetchValueByCriteria('fs_mailbox_message', 'body_html', ['id' => $messageId]);
	}

	/**
	 * Creates a Mailbox for the user and returns its ID.
	 */
	private function createMailbox(string $mb_name): int
	{
		$mb_id = 0;
		$i = 0;
		$insert_name = $mb_name;
		while (($mb_id = $this->db->insert('fs_mailbox', ['name' => strip_tags($insert_name)])) === 0) {
			++$i;
			$insert_name = $mb_name . $i;
		}

		return $mb_id;
	}
}
