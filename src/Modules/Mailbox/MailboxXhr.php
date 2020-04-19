<?php

namespace Foodsharing\Modules\Mailbox;

use Foodsharing\Helpers\TimeHelper;
use Foodsharing\Lib\Mail\AsyncMail;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Mailbox\MailboxFolder;
use Foodsharing\Permissions\MailboxPermissions;
use Foodsharing\Services\SanitizerService;

class MailboxXhr extends Control
{
	private $sanitizerService;
	private $timeHelper;
	private $mailboxGateway;
	private $mailboxPermissions;

	public function __construct(
		MailboxView $view,
		SanitizerService $sanitizerService,
		TimeHelper $timeHelper,
		MailboxGateway $mailboxGateway,
		MailboxPermissions $mailboxPermissions
	) {
		$this->view = $view;
		$this->sanitizerService = $sanitizerService;
		$this->timeHelper = $timeHelper;
		$this->mailboxGateway = $mailboxGateway;
		$this->mailboxPermissions = $mailboxPermissions;

		parent::__construct();
	}

	public function attach()
	{
		if (!$this->mailboxPermissions->mayHaveMailbox()) {
			return XhrResponses::PERMISSION_DENIED;
		}
		// is filesize (10MB) and filetype allowed?
		$attachmentIsAllowed = $this->attach_allow($_FILES['etattach']['name'], $_FILES['etattach']['type']);
		if ($attachmentIsAllowed && isset($_FILES['etattach']['size']) && $_FILES['etattach']['size'] < 1310720) {
			$new_filename = bin2hex(random_bytes(16));

			$ext = strtolower($_FILES['etattach']['name']);
			$ext = explode('.', $ext);
			if (count($ext) > 1) {
				$ext = end($ext);
				$ext = trim($ext);
				$ext = '.' . preg_replace('/[^a-z0-9]/', '', $ext);
			} else {
				$ext = '';
			}

			$new_filename = $new_filename . $ext;

			move_uploaded_file($_FILES['etattach']['tmp_name'], 'data/mailattach/tmp/' . $new_filename);

			$init = 'window.parent.mb_finishFile("' . $new_filename . '");';
		} elseif (!$attachmentIsAllowed) {
			$init = 'window.parent.pulseInfo(\'' . $this->sanitizerService->jsSafe($this->translationHelper->s('wrong_file')) . '\');window.parent.mb_removeLast();';
		} else {
			$init = 'window.parent.pulseInfo(\'' . $this->sanitizerService->jsSafe($this->translationHelper->s('file_to_big')) . '\');window.parent.mb_removeLast();';
		}

		echo '<html><head>

		<script type="text/javascript">
			function init()
			{
				' . $init . '
			}
		</script>
				
		</head><body onload="init();"></body></html>';

		exit();
	}

	public function loadmails()
	{
		if (!$this->mailboxPermissions->mayHaveMailbox()) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$last_refresh = (int)$this->mem->get('mailbox_refresh');

		$cur_time = (int)time();

		if (
			$last_refresh == 0
			||
			($cur_time - $last_refresh) > 30
		) {
			$this->mem->set('mailbox_refresh', $cur_time);
		}

		// convert folder string to int
		$farray = [
			'inbox' => MailboxFolder::FOLDER_INBOX,
			'sent' => MailboxFolder::FOLDER_SENT,
			'trash' => MailboxFolder::FOLDER_TRASH,
		];

		if (!isset($farray[$_GET['folder']])) {
			return [
				'status' => 1,
				'html' => $this->view->noMessage(),
				'append' => '#messagelist tbody'
			];
		}
		$folder = $farray[$_GET['folder']];

		$mb_id = (int)$_GET['mb'];
		if ($this->mailboxPermissions->mayMailbox($mb_id)) {
			$this->mailboxGateway->mailboxActivity($mb_id);
			$messages = $this->mailboxGateway->listMessages($mb_id, $folder);
			if (!$messages) {
				return [
					'status' => 1,
					'html' => $this->view->noMessage(),
					'append' => '#messagelist tbody'
				];
			}

			$nc_js = '';
			if ($boxes = $this->mailboxGateway->getBoxes($this->session->isAmbassador(), $this->session->id(), $this->mailboxPermissions->mayHaveMailbox())) {
				if ($newcount = $this->mailboxGateway->getNewCount($boxes)) {
					foreach ($newcount as $nc) {
						$nc_js .= '
								$( "ul.dynatree-container a.dynatree-title:contains(\'' . $nc['name'] . '@' . PLATFORM_MAILBOX_HOST . '\')" ).removeClass("nonew").addClass("newmail").text("' . $nc['name'] . '@' . PLATFORM_MAILBOX_HOST . ' (' . (int)$nc['count'] . ')");';
					}
				}
			}
			$fromToTitles = [
				MailboxFolder::FOLDER_INBOX => 'Von',
				MailboxFolder::FOLDER_SENT => 'An',
				MailboxFolder::FOLDER_TRASH => 'Von/An'
			];
			$mailbox = $this->mailboxGateway->getMailbox($mb_id);
			$currentMailboxName = isset($mailbox['email_name']) ? $mailbox['email_name'] : $mailbox['name'];

			return [
				'status' => 1,
				'html' => $this->view->listMessages($messages, $folder, $currentMailboxName),
				'append' => '#messagelist tbody',
				'script' => '
						$("#messagelist .from a:first").text("' . $fromToTitles[$folder] . '");
						$("#messagelist tbody tr").on("mouseover", function(){
							$("#messagelist tbody tr").removeClass("selected focused");
							$(this).addClass("selected focused");
							
						});
						$("#messagelist tbody tr").on("mouseout", function(){
							$("#messagelist tbody tr").removeClass("selected focused");							
						});
						$("#messagelist tbody tr").on("click", function(){
							ajreq("loadMail",{id:($(this).attr("id").split("-")[1])});
						});
						$("#messagelist tbody td").disableSelection();
						' . $nc_js . '
					'
			];
		}
	}

	public function move()
	{
		if (!$this->mailboxPermissions->mayMessage($_GET['mid'])) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$folder = $this->mailboxGateway->getMailFolderId($_GET['mid']);

		if ($folder == MailboxFolder::FOLDER_TRASH) {
			$this->mailboxGateway->deleteMessage($_GET['mid']);
		} else {
			$this->mailboxGateway->move($_GET['mid'], $_GET['f']);
		}

		return [
			'status' => 1,
			'script' => '$("tr#message-' . (int)$_GET['mid'] . '").remove();$("#message-body").dialog("close");'
		];
	}

	public function quickreply()
	{
		if (!isset($_GET['mid']) || !$this->mailboxPermissions->mayMessage($_GET['mid'])) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$mailboxId = $this->mailboxGateway->getMailboxId($_GET['mid']);
		if ($this->mailboxPermissions->mayMailbox($mailboxId)) {
			$message = $this->mailboxGateway->getMessage($_GET['mid']);
			$sender = @json_decode($message['sender'], true);
			if (isset($sender['mailbox'], $sender['host']) && $sender != null) {
				$subject = 'Re: ' . trim(str_replace(['Re:', 'RE:', 're:', 'aw:', 'Aw:', 'AW:'], '', $message['subject']));

				$data = json_decode(file_get_contents('php://input'), true);
				$body = strip_tags($data['msg']) . "\n\n\n\n--------- Nachricht von " . $this->timeHelper->niceDate($message['time_ts']) . " ---------\n\n>\t" . str_replace("\n", "\n>\t", $message['body']);

				$mail = new AsyncMail($this->mem);
				$mail->setFrom($message['mailbox'] . '@' . PLATFORM_MAILBOX_HOST, $this->session->user('name'));
				if (!empty($sender['personal'])) {
					$mail->addRecipient($sender['mailbox'] . '@' . $sender['host'], $sender['personal']);
				} else {
					$mail->addRecipient($sender['mailbox'] . '@' . $sender['host']);
				}
				$mail->setSubject($subject);
				$html = nl2br($body);
				$mail->setHTMLBody($html);
				$mail->setBody($body);
				$mail->send();

				// save message to sent folder
				$this->mailboxGateway->saveMessage(
					$mailboxId,
					MailboxFolder::FOLDER_SENT,
					json_encode([
						'host' => PLATFORM_MAILBOX_HOST,
						'mailbox' => $message['mailbox'],
						'personal' => $this->session->user('name')
					]),
					json_encode([$sender]),
					$subject,
					$body,
					$html,
					date('Y-m-d H:i:s'),
					'',
					1 // mark read
				);

				$this->mailboxGateway->setRead($message['id'], 1);
				$this->mailboxGateway->setAnswered($message['id']);

				echo json_encode([
					'status' => 1,
					'message' => 'Spitze! Die E-Mail wurde versendet.'
				]);
				exit();
			}
		}

		echo json_encode([
			'status' => 0,
			'message' => 'Die E-Mail konnte nicht gesendet werden.'
		]);
		exit();
	}

	public function send_message()
	{
		if (!$this->mailboxPermissions->mayHaveMailbox()) {
			return XhrResponses::PERMISSION_DENIED;
		}
		/*
		 *  an		an
			body	body
			mb		1
			sub		betr
		 */

		if ($last = (int)$this->mem->user($this->session->id(), 'mailbox-last')) {
			if ((time() - $last) < 15) {
				return [
					'status' => 1,
					'script' => 'pulseError("Du kannst nur eine E-Mail pro 15 Sekunden versenden, bitte warte einen Augenblick...");'
				];
			}
		}

		$this->mem->userSet($this->session->id(), 'mailbox-last', time());

		if ($this->mailboxPermissions->mayMailbox($_POST['mb'])) {
			if ($mailbox = $this->mailboxGateway->getMailbox($_POST['mb'])) {
				$an = explode(';', $_POST['an']);
				$tmp = [];
				foreach ($an as $a) {
					$trimmed = trim($a);
					$tmp[$trimmed] = $trimmed;
				}
				$an = $tmp;
				if (count($an) > 100) {
					return [
						'status' => 1,
						'script' => 'pulseError("Zu viele Empfänger");'
					];
				}
				$attach = false;

				if (isset($_POST['attach']) && is_array($_POST['attach'])) {
					$attach = [];
					foreach ($_POST['attach'] as $a) {
						if (isset($a['name'], $a['tmp'])) {
							$tmp = str_replace(['/', '\\'], '', $a['tmp']);
							$name = strtolower($a['name']);
							str_replace(['ä', 'ö', 'ü', 'ß', ' '], ['ae', 'oe', 'ue', 'ss', '_'], $name);
							$name = preg_replace('/[^a-z0-9\-\.]/', '', $name);

							if (file_exists('data/mailattach/tmp/' . $tmp)) {
								$attach[] = [
									'path' => 'data/mailattach/tmp/' . $tmp,
									'name' => $name
								];
							}
						}
					}
				}

				$this->libPlainMail(
					$an,
					[
						'email' => $mailbox['name'] . '@' . PLATFORM_MAILBOX_HOST,
						'name' => $mailbox['email_name']
					],
					$_POST['sub'],
					$_POST['body'],
					$attach
				);

				$to = [];
				foreach ($an as $a) {
					if ($this->emailHelper->validEmail($a)) {
						$t = explode('@', $a);

						$to[] = [
							'personal' => $a,
							'mailbox' => $t[0],
							'host' => $t[1]
						];
					}
				}

				if ($this->mailboxGateway->saveMessage(
					$_POST['mb'],
					MailboxFolder::FOLDER_SENT,
					json_encode([
						'host' => PLATFORM_MAILBOX_HOST,
						'mailbox' => $mailbox['name'],
						'personal' => $mailbox['email_name']
					]),
					json_encode($to),
					$_POST['sub'],
					$_POST['body'],
					nl2br($_POST['body']),
					date('Y-m-d H:i:s'),
					'',
					1
				)
				) {
					if (($mb_id = $this->mailboxGateway->getMailboxId($_POST['reply']))
						&& $this->mailboxPermissions->mayMailbox($mb_id)) {
						$this->mailboxGateway->setAnswered($_POST['reply']);
					}

					return [
						'status' => 1,
						'script' => '
									pulseInfo("' . $this->translationHelper->s('send_success') . '");
									mb_clearEditor();
									mb_closeEditor();'
					];
				}
			}
		}
	}

	public function fmail()
	{
		if (!$this->mailboxPermissions->mayMessage($_GET['id'])) {
			return XhrResponses::PERMISSION_DENIED;
		}
		$html = $this->mailboxGateway->getMessageHtmlBody($_GET['id']);
		if ($html === strip_tags($html)) {
			// Convert line breaks to brs only in non-html mails
			$html = nl2br($html);
		}

		if (strpos(strtolower($html), '<body') === false) {
			$html = '<html><head><style type="text/css">html{height:100%;background-color: white;}body,div,h1,h2,h3,h4,h5,h6,td,th,p{font-family:Arial,Helvetica,Verdana,sans-serif;}body,div,td,th,p{font-size:13px;}body{margin:0;padding:0;}</style></head><body>' . $html . '</body></html>';
		} else {
			$html = str_replace(['<body', '<BODY', '<Body'], '<body', $html);
			$html = str_replace(['<head>', '<HEAD>', '<Head>'], '<head><style type="text/css">html{height:100%;background-color: white;}body,div,h1,h2,h3,h4,h5,h6,td,th,p{font-family:Arial,Helvetica,Verdana;}body,div,td,th,p{font-size:13px;}body{margin:0;padding:0;}</style>', $html);
		}

		// $html = str_replace('href="mailto:', 'onclick="parent.mb_new_message(this.href.replace(\'mailto:\',\'\'));return false;" href="mailto:', $html);

		echo $html;
		exit();
	}

	public function loadMail()
	{
		if (!$this->mailboxPermissions->mayMessage($_GET['id'])) {
			return XhrResponses::PERMISSION_DENIED;
		}
		if ($this->mailboxPermissions->mayMailbox($this->mailboxGateway->getMailboxId($_GET['id']))) {
			$mail = $this->mailboxGateway->getMessage($_GET['id']);
			$this->mailboxGateway->setRead($_GET['id'], 1);
			$mail['attach'] = trim($mail['attach']);
			if (!empty($mail['attach'])) {
				$mail['attach'] = json_decode($mail['attach'], true);
			}

			return [
				'status' => 1,
				'html' => $this->view->message($mail),
				'append' => '#message-body',
				'script' => '
		
				bodymin = 80;
				if($("#mailattch").length > 0)
				{
					bodymin += 40;
				}
		
				$("#message-body").dialog("option",{
					title: \'' . $this->sanitizerService->jsSafe($mail['subject']) . '\',
					height: ($( window ).height()-40)
				});
				$(".mailbox-body").css({
					"height" : ($("#message-body").height()-bodymin)+"px",
					"overflow":"auto"
				});
				$(".mailbox-body-loader").css({
					"height" : ($("#message-body").height()-bodymin)+"px",
					"overflow":"auto"
				});
				$("#message-body").dialog("open");
				$("tr#message-' . (int)$_GET['id'] . ' .read-0,tr#message-' . (int)$_GET['id'] . '").addClass("read-1").removeClass("read-0");'
			];
		}
	}

	private function libPlainMail($to, $from, $subject, $message, $attach = false)
	{
		if (is_array($to) && !isset($to['name'])) {
			$email = $to;
		} elseif (is_array($to) && isset($to['email'])) {
			$email = $to['email'];
			$name = $to['name'];
		} else {
			$email = $to;
			$name = $to;
		}

		$from_email = $from;
		$from_name = $from;
		if (is_array($from)) {
			$from_email = $from['email'];
			$from_name = $from['name'];
		}

		$mail = new AsyncMail($this->mem);

		$mail->setFrom($from_email, $from_name);

		if (is_array($email)) {
			foreach ($email as $e) {
				if ($this->emailHelper->validEmail($e)) {
					$this->mailboxGateway->addContact($e, $this->session->id());
					$mail->addRecipient($e);
				}
			}
		} else {
			$mail->addRecipient($email);
		}

		$mail->setSubject($subject);

		$message = str_replace(['<br>', '<br/>', '<br />', '<p>', '</p>', '</p>'], "\r\n", $message);
		$message = strip_tags($message);

		$html = nl2br($message);
		$mail->setHTMLBody($html);

		$plainBody = $this->sanitizerService->htmlToPlain($html);
		$mail->setBody($plainBody);

		if ($attach !== false) {
			foreach ($attach as $a) {
				$mail->addAttachment($a['path'], $a['name']);
			}
		}
		$mail->send();
	}

	public function attach_allow($filename, $mime)
	{
		if (strlen($filename) < 300) {
			$ext = explode('.', $filename);
			$ext = end($ext);
			$ext = strtolower($ext);
			$notallowed = [
				'php' => true,
				'html' => true,
				'htm' => true,
				'php5' => true,
				'php4' => true,
				'php3' => true,
				'php2' => true,
				'php1' => true
			];
			$notallowed_mime = [];

			if (!isset($notallowed[$ext]) && !isset($notallowed_mime[$mime])) {
				return true;
			}
		}

		return false;
	}
}
