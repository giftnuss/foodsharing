<?php

namespace Foodsharing\Modules\Team;

use Foodsharing\Lib\Mail\AsyncMail;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\Model;
use Foodsharing\Services\OutputSanitizerService;

class TeamXhr extends Control
{
	private $gateway;
	private $outputSanitizerService;

	public function __construct(TeamGateway $gateway, Model $model, TeamView $view, OutputSanitizerService $outputSanitizerService)
	{
		$this->gateway = $gateway;
		$this->model = $model;
		$this->view = $view;
		$this->outputSanitizerService = $outputSanitizerService;

		parent::__construct();
	}

	public function contact(): void
	{
		$xhr = new Xhr();

		if ($this->ipIsBlocked(120, 'contact')) {
			$xhr->addMessage('Du hast zu viele Nachrichten versendet. Bitte warte einen Moment!', 'error');
			$xhr->send();
		}

		if ($id = $this->getPostInt('id')) {
			if ($user = $this->gateway->getUser($id)) {
				$mail = new AsyncMail();

				if ($this->func->validEmail($_POST['email'])) {
					$mail->setFrom($_POST['email']);
				} else {
					$mail->setFrom(DEFAULT_EMAIL);
				}

				$msg = $_POST['message'];
				$name = strip_tags($_POST['name']);

				$msg = 'Name: ' . $name . "\n\n" . $msg;

				$mail->setBody($msg);
				$mail->setHtmlBody($this->outputSanitizerService->sanitizeForHtmlNoMarkup($msg));
				$mail->setSubject('foodsharing.de Kontaktformular Anfrage von ' . $name);

				$mail->addRecipient($user['email']);

				$mail->send();

				$xhr->addScript('$("#contactform").parent().parent().parent().fadeOut();');
				$xhr->addMessage($this->func->s('mail_send_success'), 'success');
				$xhr->send();
			}
		}

		$xhr->addMessage($this->func->s('error'), 'error');
		$xhr->send();
	}

	/**
	 * Function to check and block an ip address.
	 *
	 * @param int $duration
	 * @param string $context
	 *
	 * @return bool
	 */
	private function ipIsBlocked($duration = 60, $context = 'default'): bool
	{
		$ip = $this->getIp();

		if ($block = $this->model->qRow('SELECT UNIX_TIMESTAMP(`start`) AS `start`,`duration` FROM fs_ipblock WHERE ip = ' . strip_tags($this->getIp()) . ' AND context = ' . strip_tags($context))) {
			if (time() < ((int)$block['start'] + (int)$block['duration'])) {
				return true;
			}
		}

		$this->model->insert('
	REPLACE INTO fs_ipblock
	(`ip`,`context`,`start`,`duration`)
	VALUES
	(' . strip_tags($ip) . ',' . strip_tags($context) . ',NOW(),' . (int)$duration . ')');

		return false;
	}

	private function getIp()
	{
		if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['REMOTE_ADDR'];
		}

		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
}
