<?php

namespace Foodsharing\Helpers;

use Flourish\fFile;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Mail\AsyncMail;
use Foodsharing\Modules\Core\InfluxMetrics;
use Foodsharing\Modules\EmailTemplateAdmin\EmailTemplateAdminGateway;
use Foodsharing\Services\SanitizerService;

class MailingHelper
{
	private $mem;
	/**
	 * @var InfluxMetrics
	 */
	private $metrics;
	private $emailTemplateAdminGateway;
	private $sanitizerService;

	public function __construct(
		EmailTemplateAdminGateway $emailTemplateAdminGateway,
		InfluxMetrics $metrics,
		Mem $mem,
		SanitizerService $sanitizerService
	) {
		$this->emailTemplateAdminGateway = $emailTemplateAdminGateway;
		$this->mem = $mem;
		$this->metrics = $metrics;
		$this->sanitizerService = $sanitizerService;
	}

	private function emailBodyTpl(string $message, $email = false, $token = false): string
	{
		$unsubscribe = '
	<tr>
		<td height="20" valign="top" style="background-color:#FAF7E5">
			<div style="text-align:center;padding-top:10px;font-size:11px;font-family:Arial;padding:15px;color:#594129;">
				Willst Du diese Art von Benachrichtigungen nicht mehr bekommen? Du kannst unter <a style="color:#F36933" href="' . BASE_URL . '/?page=settings&sub=info" target="_blank">Benachrichtigungen</a> einstellen, welche Mails Du erhältst. 
			</div>
		</td>
	</tr>';

		if ($email !== false && $token !== false) {
			$unsubscribe = '
		<tr>
			<td height="20" valign="top" style="background-color:#FAF7E5">
				<div style="text-align:center;padding-top:10px;font-size:11px;font-family:Arial;padding:15px;color:#594129;">
					Möchtest Du keinen Newsletter mehr erhalten? <a style="color:#F36933" href="https://foodsharing.de/?page=login&sub=unsubscribe&t=' . $token . '&e=' . $email . '" target="_blank">Klicke hier zum Abbestellen!</a> Du kannst unter <a style="color:#F36933" href="https://foodsharing.de/?page=settings&sub=info" target="_blank">Benachrichtigungen</a> einstellen, welche Mails Du erhältst.
				</div>
<p style="font-size:11px;"><strong>Impressum</strong><br />
Angaben gemäß § 5 TMG:<br />
<br />foodsharing e.<span style="white-space:nowrap">&thinsp;</span>V.<br/>
Marsiliusstr. 36<br />
50937 Köln<br />
Vertreten durch:<br /><br />
Frank Bowinkelmann<br />
Kontakt:<br />E-Mail: info@foodsharing.de<br />
Registereintrag:<br /><br />Eintragung im Vereinsregister<br />
Registergericht: Amtsgericht Köln<br />
Registernummer: VR 17439<br />
Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV:<br />
<br />Frank Bowinkelmann<br /></p>
			</td>
		</tr>';
		}

		$message = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $message);

		$search = array('<a', '<td', '<li');
		$replace = array('<a style="color:#F36933"', '<td style="font-size:13px;font-family:Arial;color:#31210C;"', '<li style="margin-bottom:11px"');

		return '<html><head><style type="text/css">a{text-decoration:none;}a:hover{text-decoration:underline;}a.button{display:inline-block;padding:6px 16px;border:1px solid #FFFFFF;background-color:#4A3520;color:#FFFFFF !important;font-weight:bold;border-radius:8px;}a.button:hover{border:1px solid #4A3520;background-color:#ffffff;color:#4A3520 !important;text-decoration:none !important;}.border{padding:10px;border-top:1px solid #4A3520;border-bottom:1px solid #4A3520;background-color:#FFFFFF;}</style></head>
	<body style="margin:0;padding:0;">
		<div style="background-color:#F1E7C9;border:1px solid #628043;border-top:0px;padding:2%;padding-top:0;margin-top:0px;">

<table width="100%" style="margin-bottom:10px;margin-top:-2px;">
<tr>
				<td valign="top" height="30" style="background-color:#4A3520">
					<div style="padding:5px;font-size:13px;font-family:Arial;color:#FAF7E5;overflow:hidden;" align="left">
						<a style="display:block;color:#FAF7E5;text-decoration:none;" href="https://foodsharing.de/" target="_blank">
							<span style="margin-left:10px;font-size:20px;font-family:Arial Black, Arial;font-weight:bold;color:#FAF7E5;letter-spacing:-1px;">food</span><span style="margin-right:10px;font-size:20px;font-family:Arial Black, Arial;font-weight:bold;color:#4D971E;letter-spacing:-1px">sharing</span><span style="color:#F36933">.</span>de
						</a>
					</div>
				</td></tr>
</table>
			<table height="100%" width="100%">
				<tr>
				<td valign="top" style="background-color:#FAF7E5">
					<div style="padding:5px;font-size:13px;font-family:Arial;padding:15px;color:#31210C;">
						' . str_replace($search, $replace, $message) . '
					</div>
				</td>
				</tr>
				' . $unsubscribe . '
			</table>
		</div>
	</body>
</html>';
	}

	public function tplMail($tpl_id, $to, $var = array(), $from_email = false)
	{
		$mail = new AsyncMail($this->mem);

		if ($from_email !== false && $this->validEmail($from_email)) {
			$mail->setFrom($from_email);
		} else {
			$mail->setFrom(DEFAULT_EMAIL, DEFAULT_EMAIL_NAME);
		}

		$message = $this->emailTemplateAdminGateway->getOne_message_tpl($tpl_id);

		$search = array();
		$replace = array();
		foreach ($var as $key => $v) {
			$search[] = '{' . strtoupper($key) . '}';
			$replace[] = $v;
		}

		$message['body'] = str_replace($search, $replace, $message['body']);

		$message['subject'] = str_replace($search, $replace, $message['subject']);
		if (!$message['subject']) {
			$message['subject'] = 'foodsharing-Mail';
		}

		$mail->setSubject($this->sanitizerService->htmlToPlain($message['subject']));
		$htmlBody = $this->emailBodyTpl($message['body']);
		$mail->setHTMLBody($htmlBody);

		// playintext body
		$plainBody = $this->sanitizerService->htmlToPlain($htmlBody);
		$mail->setBody($plainBody);

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
