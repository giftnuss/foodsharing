<?php

namespace Foodsharing\Modules\Settings;

use DateTime;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\SleepStatus;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Login\LoginGateway;
use Foodsharing\Modules\Mails\MailsGateway;

class SettingsXhr extends Control
{
	private $foodsaverGateway;
	private $loginGateway;
	private $settingsGateway;
	private $mailsGateway;

	public function __construct(SettingsView $view, SettingsGateway $settingsGateway, FoodsaverGateway $foodsaverGateway, LoginGateway $loginGateway, MailsGateway $mailsGateway)
	{
		$this->view = $view;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->loginGateway = $loginGateway;
		$this->settingsGateway = $settingsGateway;
		$this->mailsGateway = $mailsGateway;

		parent::__construct();

		if (!$this->session->may()) {
			return false;
		}
	}

	public function changemail()
	{
		if ($this->session->may()) {
			$dia = new XhrDialog();
			$dia->setTitle('E-Mail-Adresse ändern');

			$dia->addContent($this->view->changeMail());

			$dia->addButton('E-Mail-Adresse ändern', 'ajreq(\'changemail2\',{email:$(\'#newmail\').val()});');

			return $dia->xhrout();
		}

		echo '0';
		die();
	}

	public function changemail2()
	{
		$emailAddress = $_GET['email'];
		if (!$this->emailHelper->validEmail($emailAddress)) {
			return [
				'status' => 1,
				'script' => 'pulseInfo("' . $this->translationHelper->s('newmail_invalid') . '");'
			];
		}
		if ($this->emailHelper->isFoodsharingEmailAddress($emailAddress)) {
			return [
				'status' => 1,
				'script' => 'pulseInfo("' . $this->translationHelper->s('newmail_illegal_domain') . '");'
			];
		}
		if ($this->foodsaverGateway->emailExists($emailAddress)) {
			return [
				'status' => 1,
				'script' => 'pulseError("' . $this->translationHelper->s('newmail_in_use') . '");'
			];
		}

		$token = bin2hex(random_bytes(16));
		$this->settingsGateway->addNewMail($this->session->id(), $emailAddress, $token);

		if ($fs = $this->foodsaverGateway->getFoodsaverBasics($this->session->id())) {
			$this->mailsGateway->removeBounceForMail($emailAddress);
			$this->emailHelper->tplMail('user/change_email', $emailAddress, [
				'anrede' => $this->translationHelper->genderWord($fs['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
				'name' => $fs['name'],
				'link' => BASE_URL . '/?page=settings&sub=general&newmail=' . $token
			]);

			return [
				'status' => 1,
				'script' => 'pulseInfo("' . $this->translationHelper->s('newmail_sent') . '",{sticky:true});'
			];
		}
	}

	public function changemail3()
	{
		if ($email = $this->settingsGateway->getMailChange($this->session->id())) {
			$dia = new XhrDialog();
			$dia->setTitle('E-Mail-Adresse ändern');

			$dia->addContent($this->view->changemail3($email));

			$dia->addButton('Abbrechen', 'ajreq(\'abortchangemail\');$(\'#' . $dia->getId() . '\').dialog(\'close\');');
			$dia->addButton('Bestätigen', 'ajreq(\'changemail4\',{pw:$(\'#passcheck\').val(),did:\'' . $dia->getId() . '\'});');

			return $dia->xhrout();
		}
	}

	public function abortchangemail()
	{
		$this->settingsGateway->abortChangemail($this->session->id());
	}

	public function changemail4()
	{
		$fsId = $this->session->id();
		if ($currentEmail = $this->foodsaverGateway->getEmailAddress($fsId)) {
			$did = strip_tags($_GET['did']);
			if ($this->loginGateway->checkClient($currentEmail, $_GET['pw'])) {
				if ($newEmail = $this->settingsGateway->getMailChange($fsId)) {
					if ($this->settingsGateway->changeMail($fsId, $newEmail) > 0) {
						$this->settingsGateway->logChangedSetting($fsId, ['email' => $this->session->user('email')], ['email' => $newEmail], ['email']);

						return [
							'status' => 1,
							'script' => 'pulseInfo("Deine E-Mail-Adresse wurde geändert!");$("#' . $did . '").dialog("close");'
						];
					}

					return [
						'status' => 1,
						'script' => 'pulseInfo(\'Die E-Mail-Adresse konnte nicht geändert werden, jemand anderes benutzt sie schon!\');'
					];
				}
			}
		}

		return [
			'status' => 1,
			'script' => 'pulseError("Das Passwort wahl wohl falsch, vertippt?");$("#passcheck").val("");$("#passcheck")[0].focus();'
		];
	}

	public function sleepmode()
	{
		/*
		 * from
		 * until
			msg
			status	2

		 */

		$from = '';
		$to = '';
		$msg = '';

		$states = [
			SleepStatus::NONE => true,
			SleepStatus::TEMP => true,
			SleepStatus::FULL => true
		];

		if (isset($_POST['from']) && $date = DateTime::createFromFormat('d.m.Y', $_POST['from'])) {
			$from = $date->format('Y-m-d H:i:s');
		}
		if (isset($_POST['until']) && $date = DateTime::createFromFormat('d.m.Y', $_POST['until'])) {
			$to = $date->format('Y-m-d H:i:s');
		}
		if ($txt = $this->getPostString('msg')) {
			$msg = $txt;
		}
		$xhr = new Xhr();
		$xhr->setStatus(0);
		if (isset($states[$_POST['status']])) {
			$status = (int)$_POST['status'];

			$this->settingsGateway->updateSleepMode($this->session->id(), $status, $from, $to, $msg);

			$xhr->setStatus(1);
		}

		$xhr->send();
	}
}
