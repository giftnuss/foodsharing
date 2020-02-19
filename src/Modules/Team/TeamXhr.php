<?php

namespace Foodsharing\Modules\Team;

use Foodsharing\Lib\Mail\AsyncMail;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Services\SanitizerService;

class TeamXhr extends Control
{
	private $gateway;
	private $sanitizerService;

	public function __construct(TeamGateway $gateway, TeamView $view, SanitizerService $sanitizerService)
	{
		$this->gateway = $gateway;
		$this->view = $view;
		$this->sanitizerService = $sanitizerService;

		parent::__construct();
	}

	public function contact()
	{
		$xhr = new Xhr();

		$minutesWaiting = 10;

		if ($this->gateway->isABlockedIP($minutesWaiting * 60, 'contact')) {
			$xhr->addMessage($this->translationHelper->sv('wait-var-minutes-till-next-message', ['minutes' => $minutesWaiting]), 'error');
			$xhr->send();
		}

		if ($id = $this->getPostInt('id')) {
			if ($user = $this->gateway->getUser($id)) {
				if (!$user['contact_public']) {
					return XhrResponses::PERMISSION_DENIED;
				}

				$mail = new AsyncMail($this->mem);

				if ($this->emailHelper->validEmail($_POST['email'])) {
					$mail->setFrom($_POST['email']);
				} else {
					$mail->setFrom(DEFAULT_EMAIL);
				}

				$senderName = strip_tags($_POST['name']);

				$msg = 'Name: ' . $senderName . "\n";
				$msg .= $this->translationHelper->s('email') . ': ' . strip_tags($_POST['email']) . "\n\n";
				$msg .= $_POST['message'];

				$mail->setBody($msg);
				$mail->setHtmlBody($this->sanitizerService->plainToHtml($msg));
				$mail->setSubject($this->translationHelper->sv('var-name-send-fs-contact-message', ['name' => $senderName]));

				$mail->addRecipient($user['email']);

				$mail->send();

				$xhr->addScript('$("#contactform").parent().parent().parent().fadeOut();');
				$xhr->addMessage($this->translationHelper->s('mail_send_success'), 'success');
				$xhr->send();
			}
		}

		$xhr->addMessage($this->translationHelper->s('error'), 'error');
		$xhr->send();
	}
}
