<?php

namespace Foodsharing\Modules\Team;

use Foodsharing\Lib\Mail\AsyncMail;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Utility\Sanitizer;

class TeamXhr extends Control
{
	private $gateway;
	private $sanitizerService;

	public function __construct(
		TeamGateway $gateway,
		TeamView $view,
		Sanitizer $sanitizerService
	) {
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
			$xhr->addMessage($this->translator->trans('team.ratelimit', ['{minutes}' => $minutesWaiting]), 'error');
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

				$msg = $this->translator->trans('team.name') . ': ' . $senderName . "\n";
				$msg .= $this->translator->trans('team.email') . ': ' . strip_tags($_POST['email']) . "\n\n";
				$msg .= $_POST['message'];

				$mail->setBody($msg);
				$mail->setHtmlBody($this->sanitizerService->plainToHtml($msg));
				$mail->setSubject($this->translator->trans('team.contact', ['{name}' => $senderName]));

				$mail->addRecipient($user['email']);

				$mail->send();

				$xhr->addScript('$("#contactform").parent().parent().parent().fadeOut();');
				$xhr->addMessage($this->translator->trans('team.contacted'), 'success');
				$xhr->send();
			}
		}

		$xhr->addMessage($this->translator->trans('error_unexpected'), 'error');
		$xhr->send();
	}
}
