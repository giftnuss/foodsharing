<?php

namespace Foodsharing\Modules\Settings;

use DateTime;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Login\LoginGateway;

class SettingsXhr extends Control
{
	private $foodsaverGateway;
	private $loginGateway;
	private $settingsGateway;

	public function __construct(SettingsView $view, SettingsGateway $settingsGateway, FoodsaverGateway $foodsaverGateway, LoginGateway $loginGateway)
	{
		$this->view = $view;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->loginGateway = $loginGateway;
		$this->settingsGateway = $settingsGateway;

		parent::__construct();

		if (!$this->session->may()) {
			return false;
		}
	}

	public function changemail()
	{
		if ($this->session->mayLegacy()) {
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
		if ($this->emailHelper->validEmail($_GET['email'])) {
			if ($this->foodsaverGateway->emailExists($_GET['email'])) {
				return array(
					'status' => 1,
					'script' => 'pulseError("Diese E-Mail-Adresse benutzt bereits jemand anderes.");'
				);
			}
			$token = bin2hex(random_bytes(16));
			$this->settingsGateway->addNewMail($this->session->id(), $_GET['email'], $token);
			// anrede name link

			if ($fs = $this->foodsaverGateway->getFoodsaverBasics($this->session->id())) {
				$this->emailHelper->tplMail('user/change_email', $_GET['email'], array(
					'anrede' => $this->translationHelper->genderWord($fs['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
					'name' => $fs['name'],
					'link' => BASE_URL . '/?page=settings&sub=general&newmail=' . $token
				));

				return array(
					'status' => 1,
					'script' => 'pulseInfo(\'Gehe jetzt zu Deinem <strong>neuen</strong> E-Mail-Postfach, um die Adresse zu bestätigen!\',{sticky:true});'
				);
			}
		} else {
			return array(
				'status' => 1,
				'script' => 'pulseInfo(\'Mit der eingegebenen E-Mail-Adresse stimmt etwas nicht.\');'
			);
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
		if ($email = $this->foodsaverGateway->getEmailAddress($fsId)) {
			$did = strip_tags($_GET['did']);
			if ($this->loginGateway->checkClient($email, $_GET['pw'])) {
				if ($email = $this->settingsGateway->getMailChange($fsId)) {
					if ($this->settingsGateway->changeMail($fsId, $email) > 0) {
						$this->settingsGateway->logChangedSetting($fsId, ['email' => $this->session->user('email')], ['email' => $email], ['email']);

						return array(
							'status' => 1,
							'script' => 'pulseInfo("Deine E-Mail-Adresse wurde geändert!");$("#' . $did . '").dialog("close");'
						);
					}

					return array(
						'status' => 1,
						'script' => 'pulseInfo(\'Die E-Mail-Adresse konnte nicht geändert werden, jemand anderes benutzt sie schon!\');'
					);
				}
			}
		}

		return array(
			'status' => 1,
			'script' => 'pulseError("Das Passwort wahl wohl falsch, vertippt?");$("#passcheck").val("");$("#passcheck")[0].focus();'
		);
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

		$states = array(
			0 => true, // normal available
			1 => true, // not available for a while
			2 => true // not available unsure how long
		);

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
