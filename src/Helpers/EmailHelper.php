<?php

namespace Foodsharing\Helpers;

use Flourish\fFile;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Mail\AsyncMail;
use Foodsharing\Modules\Core\InfluxMetrics;
use Foodsharing\Services\SanitizerService;
use Twig\Environment;

final class EmailHelper
{
	private $mem;
	/**
	 * @var InfluxMetrics
	 */
	private $metrics;
	private $emailTemplateAdminGateway;
	private $sanitizerService;
	private $twig;

	public function __construct(
		InfluxMetrics $metrics,
		Mem $mem,
		SanitizerService $sanitizerService,
		Environment $twig
	) {
		$this->mem = $mem;
		$this->metrics = $metrics;
		$this->sanitizerService = $sanitizerService;
		$this->twig = $twig;
	}

	private function emailBodyTpl(string $message, $email = false, $token = false): string
	{
		$unsubscribe = $this->twig->render('emailTemplates/unsubscribe.html.twig', []);

		if ($email !== false && $token !== false) {
			$unsubscribe = $this->twig->render('emailTemplates/unsubscribe_newsletter.html.twig', array('TOKEN' => $token, 'EMAIL' => $email));
		}

		$message = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $message);

		$search = array('<a', '<td', '<li');
		$replace = array('<a style="color:#F36933"', '<td style="font-size:13px;font-family:Arial;color:#31210C;"', '<li style="margin-bottom:11px"');
		$message = str_replace($search, $replace, $message);

		return $this->twig->render('emailTemplates/body.html.twig', array('MESSAGE' => $message, 'UNSUBSCRIBE' => $unsubscribe));
	}

	public function tplMail($tpl_id, $to, $var = array(), $from_email = false)
	{
		$mail = new AsyncMail($this->mem);

		if ($from_email !== false && $this->validEmail($from_email)) {
			$mail->setFrom($from_email);
		} elseif (array_key_exists('sender', $var)) {
			$mail->setFrom(DEFAULT_EMAIL, $var['sender'] . ' via ' . DEFAULT_EMAIL_NAME);
		} elseif (array_key_exists('poster', $var)) {
			$mail->setFrom(DEFAULT_EMAIL, $var['poster'] . ' via ' . DEFAULT_EMAIL_NAME);
		} else {
			$mail->setFrom(DEFAULT_EMAIL, DEFAULT_EMAIL_NAME);
		}

		$locale = 'de-de';
		$tpl_prefix = 'emailTemplates/' . $tpl_id . '.' . $locale;
		$message = array(
			'subject' => $this->twig->render($tpl_prefix . '.subject.twig', []),
			'body' => $this->twig->render($tpl_prefix . '.body.html.twig', [])
		);

		$search = array();
		$replace = array();
		foreach ($var as $key => $v) {
			$search[] = '{' . strtoupper($key) . '}';
			$replace[] = $v;
		}

		$message['body'] = str_replace($search, $replace, $message['body']);

		$htmlBody = $this->emailBodyTpl($message['body']);
		$mail->setHTMLBody($htmlBody);

		// playintext body
		$plainBody = $this->sanitizerService->htmlToPlain($htmlBody);
		$mail->setBody($plainBody);

		$message['subject'] = str_replace($search, $replace, $message['subject']);
		if (!$message['subject']) {
			$message['subject'] = 'foodsharing-Mail: {EXCERPT}';
		}

		$message['subject'] = $this->sanitizerService->htmlToPlain($message['subject']);  // Probably redundant, but just in case.
		$excerptAmount = substr_count($message['subject'], '{EXCERPT}'); // So we can calculate the proper subject length
		if ($excerptAmount > 0) {
			$plainMessage = $this->sanitizerService->htmlToPlain($message['body']);
			$subjectLength = strlen($message['subject']) - strlen('{EXCERPT}') * $excerptAmount;
			$excerpt = substr($plainMessage, 0, intdiv(78 - $subjectLength, $excerptAmount)); // yes, magic number. It's the RFC recommendation.
			$message['subject'] = str_replace('{EXCERPT}', $excerpt, $message['subject']);
		}

		$mail->setSubject($this->sanitizerService->htmlToPlain($message['subject']));

		if (is_iterable($to)) {
			foreach ($to as $recipient) {
				$mail->addRecipient($recipient);
			}
		} else {
			$mail->addRecipient($to);
		}
		$mail->send();
		$this->metrics->addPoint('outgoing_email', ['template' => $tpl_id], ['count' => 1]);
	}

	public function validEmail(string $email): bool
	{
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return true;
		}

		return false;
	}

	public function libmail($bezirk, $email, $subject, $message, $attach = false, $token = false)
	{
		if ($bezirk === false) {
			$bezirk = array(
				'email' => DEFAULT_EMAIL,
				'email_name' => DEFAULT_EMAIL_NAME
			);
		} elseif (!is_array($bezirk)) {
			$bezirk = array(
				'email' => $bezirk,
				'email_name' => $bezirk
			);
		} else {
			if (!$this->validEmail($bezirk['email'])) {
				$bezirk['email'] = EMAIL_PUBLIC;
			}
			if (empty($bezirk['email_name'])) {
				$bezirk['email_name'] = EMAIL_PUBLIC_NAME;
			}
		}

		if (!$this->validEmail($email)) {
			return false;
		}

		$mail = new AsyncMail($this->mem);
		$mail->setFrom($bezirk['email'], $bezirk['email_name']);
		$mail->addRecipient($email);
		if (!$subject) {
			$subject = 'foodsharing-Mail';
		}
		$mail->setSubject($subject);
		$htmlBody = $this->emailBodyTpl($message, $email, $token);
		$mail->setHTMLBody($htmlBody);

		$plainBody = $this->sanitizerService->htmlToPlain($htmlBody);
		$mail->setBody($plainBody);

		if ($attach !== false) {
			foreach ($attach as $a) {
				$mail->addAttachment(new fFile($a['path']), $a['name']);
			}
		}

		$mail->send();
	}
}
