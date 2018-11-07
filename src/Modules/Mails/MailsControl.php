<?php

namespace Foodsharing\Modules\Mails;

use Ddeboer\Imap\Server;
use Flourish\fEmail;
use Flourish\fFile;
use Flourish\fSMTP;
use Foodsharing\Modules\Console\ConsoleControl;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Mailbox\MailboxModel;

class MailsControl extends ConsoleControl
{
	/**
	 * @var fSMTP
	 */
	public static $smtp = false;
	public static $last_connect;
	private $mailboxModel;
	private $database;

	public function __construct(MailsModel $model, MailboxModel $mailboxModel, Database $database)
	{
		echo "creating mailscontrl!!!!\n";
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
		self::$smtp = false;
		$this->model = $model;
		$this->mailboxModel = $mailboxModel;
		$this->database = $database;
		parent::__construct();
		echo "-------------------------------------\n";
	}

	public function queueWorker()
	{
		$this->mem->ensureConnected();
		while (1) {
			$elem = $this->mem->cache->brpoplpush('workqueue', 'workqueueprocessing', 10);
			if ($elem !== false && $e = unserialize($elem)) {
				if ($e['type'] == 'email') {
					$res = $this->handleEmail($e['data']);
					// very basic email rate limit
					usleep(100000);
				} else {
					$res = false;
				}

				if ($res) {
					$this->mem->cache->lrem('workqueueprocessing', $elem, 1);
				} else {
					// TODO handle failed tasks?
				}
			}
		}
	}

	/**
	 * This Method will check for new E-Mails and sort it to the mailboxes.
	 */
	public function mailboxupdate()
	{
		$server = new Server(IMAP_HOST);
		$connection = $server->authenticate(IMAP_USER, IMAP_PASS);

		$mailbox = $connection->getMailbox('INBOX');
		$messages = $mailbox->getMessages();
		if (count($messages) > 0) {
			self::info(count($messages) . ' in Inbox');

			$progressbar = $this->progressbar(count($messages));

			$have_send = [];
			$i = 0;
			try {
				foreach ($messages as $msg) {
					++$i;
					$progressbar->update($i);
					$mboxes = [];
					$recipients = $msg->getTo() + $msg->getCc() + $msg->getBcc();
					foreach ($recipients as $to) {
						if (in_array(strtolower($to->getHostname()), MAILBOX_OWN_DOMAINS)) {
							$mboxes[] = $to->getMailbox();
						}
					}

					if (empty($mboxes)) {
						$msg->delete();
						continue;
					}

					$mb_ids = $this->model->getMailboxIds($mboxes);

					if (!$mb_ids) {
						$mb_ids = $this->model->getMailboxIds(array('lost'));
					}

					if ($mb_ids) {
						try {
							$html = $msg->getBodyHtml();
						} catch (\Exception $e) {
							$html = null;
							echo 'Could not get HTML body ' . $e->getMessage() . ', continuing with PLAIN TEXT\n';
						}

						if ($html) {
							$h2t = new \Html2Text\Html2Text($html);
							$body = $h2t->get_text();
							$html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
						} else {
							try {
								$text = $msg->getBodyText();
							} catch (\Exception $e) {
								$text = null;
								echo 'Could not get PLAIN TEXT body ' . $e->getMessage() . ', skipping mail.\n';
							}
							if ($text != null) {
								$body = $text;
								$html = nl2br($this->func->autolink($text));
							} else {
								continue;
							}
						}

						$attach = array();
						foreach ($msg->getAttachments() as $a) {
							$filename = $a->getFilename();
							if ($this->attach_allow($filename, null)) {
								$new_filename = uniqid();
								$path = 'data/mailattach/';
								$j = 0;
								while (file_exists($path . $new_filename)) {
									++$j;
									$new_filename = $j . '-' . $filename;
								}
								try {
									file_put_contents($path . $new_filename, $a->getDecodedContent());
									$attach[] = [
										'filename' => $new_filename,
										'origname' => $filename,
										'mime' => null
									];
								} catch (\Exception $e) {
									echo 'Could not parse/save an attachment (' . $e->getMessage() . "), skipping that one...\n";
								}
							}
						}
						$attach = json_encode($attach);

						foreach ($mb_ids as $id) {
							if (!isset($have_send[$id])) {
								$have_send[$id] = [];
							}
							$date = null;
							try {
								$date = $msg->getDate();
							} catch (\Exception $e) {
								echo 'Error parsing date: ' . $e->getMessage() . ", continuing with 'now'\n";
							}
							if ($date === null) {
								$date = new \DateTime();
							}

							$md = $date->format('Y-m-d H:i:s') . ':' . $msg->getSubject();
							if (!isset($have_send[$id][$md])) {
								$have_send[$id][$md] = true;
								$from = [];
								$from['mailbox'] = $msg->getFrom()->getMailbox();
								$from['host'] = $msg->getFrom()->getHostname();
								$name = $msg->getFrom()->getName();
								if ($name) {
									$from['personal'] = $msg->getFrom()->getName();
								}

								$this->model->saveMessage(
									$id, // mailbox id
									1, // folder
									json_encode($from), // sender
									json_encode(array_map(function ($r) {
										return ['mailbox' => $r->getMailbox(), 'host' => $r->getHostname()];
									}, $recipients)), // all recipients
									strip_tags($msg->getSubject()), // subject
									$body,
									$html,
									$date->format('Y-m-d H:i:s'),
									$attach,
									0,
									0
								);
							}
						}
					}

					$msg->delete();
				}
			} catch (\Exception $e) {
				echo 'Something went wrong, ' . $e->getMessage() . "\n";
			} finally {
				$connection->expunge();
			}

			echo "\n";
			self::success('ready :o)');
		}
	}

	private function getMailAddressParts($str)
	{
		$parts = explode('@', trim($str));
		if (count($parts) != 2) {
			throw new \Exception($str . ' is not a valid email address');
		}
		$part['mailbox'] = $parts[0];
		$part['host'] = $parts[1];

		return $part;
	}

	public function fixWrongMailSenderFormat()
	{
		$res = $this->database->fetchAll('SELECT id, sender, `to` FROM fs_mailbox_message WHERE id < 185882 AND id > 175000');
		foreach ($res as $r) {
			$sender = json_decode($r['sender']);
			$to = json_decode($r['to']);
			if (is_string($sender)) {
				$newSender = json_encode($this->getMailAddressParts($sender));
				$newTo = [];
				foreach ($to as $recip) {
					if (strpos($recip, ';')) {
						foreach (explode(';', $recip) as $rp) {
							$newTo[] = $this->getMailAddressParts($rp);
						}
					} else {
						$newTo[] = $this->getMailAddressParts($recip);
					}
				}
				$newTo = json_encode($newTo);
				$this->database->update('fs_mailbox_message', ['sender' => $newSender, 'to' => $newTo], ['id' => $r['id']]);
			}
		}
	}

	private function attach_allow($filename, $mime)
	{
		if (strlen($filename) < 300) {
			$ext = explode('.', $filename);
			$ext = end($ext);
			$ext = strtolower($ext);
			$notallowed = array(
				'php' => true,
				'html' => true,
				'htm' => true,
				'php5' => true,
				'php4' => true,
				'php3' => true,
				'php2' => true,
				'php1' => true
			);
			$notallowed_mime = array();

			if (!isset($notallowed[$ext]) && !isset($notallowed_mime[$mime])) {
				return true;
			}
		}

		return false;
	}

	public function handleEmail($data)
	{
		self::info('mail arrived ...: ' . $data['from'][0] . '@' . $data['from'][1]);
		$email = new fEmail();
		$email->setFromEmail($data['from'][0], $data['from'][1]);
		$subject = preg_replace('/\s+/', ' ', trim($data['subject']));
		$email->setSubject($subject);
		$email->setHTMLBody($data['html']);
		$email->setBody($data['body']);

		if (!empty($data['attachments'])) {
			foreach ($data['attachments'] as $a) {
				$file = new fFile($a[0]);

				// only files smaller 10 MB
				if ($file->getSize() < 1310720) {
					$email->addAttachment($file, $a[1]);
				}
			}
		}
		$has_recip = false;
		foreach ($data['recipients'] as $r) {
			// check is it own lmr email? put direct into db
			$r[0] = strtolower($r[0]);
			self::info(substr(
				$r[0],
				(strlen(DEFAULT_EMAIL_HOST) * -1),
				strlen(DEFAULT_EMAIL_HOST)
			));
			if (
				substr(
					$r[0],
					(strlen(DEFAULT_EMAIL_HOST) * -1),
					strlen(DEFAULT_EMAIL_HOST)
				) == DEFAULT_EMAIL_HOST
			) {
				self::info($r[0] . ' own host save direct into db');

				$mailbox = str_replace('@' . DEFAULT_EMAIL_HOST, '', $r[0]);

				$mb_id = $this->model->getMailboxId($mailbox);
				if (!$mb_id) {
					// lost mailbox id
					$mb_id = 25631;
				}

				$toarr = array();
				foreach ($data['recipients'] as $r) {
					$toarr[] = self::parseEmailAddress($r[0], $r[1]);
				}

				$this->model->saveMessage(
					$mb_id, // mailbox id
					1, // folder inbox
					json_encode(self::parseEmailAddress($data['from'][0], $data['from'][1])), // sender
					json_encode($toarr), // to
					$data['subject'], // subject
					$data['body'],
					$data['html'],
					date('Y-m-d H:i:s') // time,
				);
			} else {
				$email->addRecipient($r[0], $r[1]);
				$has_recip = true;
			}
		}
		if (!$has_recip) {
			return true;
		}

		// reconnect first time and force after 60 seconds inactive
		if (self::$smtp === false || (time() - self::$last_connect) > 60) {
			self::smtpReconnect();
		}

		$max_try = 3;
		$sended = false;
		while (!$sended) {
			--$max_try;
			try {
				self::info('send email tries remaining ' . ($max_try));
				$email->send(self::$smtp);
				self::success('email send OK');

				// remove atachements from temp folder
				if (!empty($data['attachments'])) {
					foreach ($data['attachments'] as $a) {
						@unlink($a[0]);
					}
				}

				return true;
				$sended = true;
				break;
			} catch (\Exception $e) {
				self::smtpReconnect();
				self::error('email send error: ' . $e->getMessage());
				self::error(print_r($data, true));
			}

			if ($max_try == 0) {
				return false;
				break;
			}
		}

		return true;
	}

	public static function parseEmailAddress($email, $name = false)
	{
		$p = explode('@', $email);

		if ($name === false) {
			$name = $email;
		}

		return array(
			'personal' => $name,
			'mailbox' => $p[0],
			'host' => $p[1]
		);
	}

	/**
	 * checks current status and renew the connection to smtp server.
	 */
	public static function smtpReconnect()
	{
		self::info('SMTP reconnect.. ');
		try {
			if (self::$smtp !== false) {
				self::info('close smtp and sleep 5 sec ...');
				@self::$smtp->close();
				//sleep(5);
			}

			self::info('connect...');
			self::$smtp = new fSMTP(SMTP_HOST, SMTP_PORT);
			//MailsControl::$smtp->authenticate(SMTP_USER, SMTP_PASS);
			self::$last_connect = time();

			self::success('reconnect OK');

			return true;
		} catch (\Exception $e) {
			self::error('reconnect failed: ' . $e->getMessage());

			return false;
		}

		return true;
	}
}
