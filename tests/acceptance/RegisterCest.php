<?php

class RegisterCest
{
	private $email;
	private $stripped_email;
	private $first_name;
	private $last_name;
	private $password;
	private $birthdate;

	public function _before()
	{
		$this->email = '     ' . sq('email') . '@test.com      ';
		$this->stripped_email = sq('email') . '@test.com';
		$this->first_name = sq('first_name');
		$this->last_name = sq('last_name');
		$this->password = sq('password');
		$this->birthdate = '1991-04-27';
		$this->$mobile_number = sq('mobile_number');
	}

	public function _after()
	{
	}

	// tests

	public function canRegisterNewUser(AcceptanceTester $I)
	{
		// create some unique values for our new user

		$I->wantTo('ensure I can register and will not receive newsletter by default');
		$I->amOnPage('/');

		// click signup, then press next on the first dialog

		$I->click('Mach mit!');
		$I->click('Jetzt registrieren!');
		$I->waitForElementVisible('#joinform', 4);
		$I->waitForElementVisible('#joinform .step.step0', 4);
		$I->click('weiter', '.step.step0');

		// fill in basic details

		$I->waitForElementVisible('#joinform .step.step1', 4);
		$I->fillField('login_name', $this->first_name);
		$I->fillField('login_surname', $this->last_name);
		$I->fillField('login_email', $this->email);
		/* workaround because chromedriver fails to fill a date field... */
		$I->executeJS("document.querySelector('#birthdate').value = '" . $this->birthdate . "'");
		$I->fillField('#login_passwd1', $this->password);
		$I->fillField('#login_passwd2', $this->password);
		$I->click('weiter', '.step.step1');

		// it gives us an alert to complain we did not upload a photo
		// whatever, I'm in a hurry

		$I->seeInPopup('Du hast kein Foto hochgeladen.');
		$I->acceptPopup();

		// skip the step with the address map, it is optional

		$I->waitForElementVisible('#joinform .step.step2', 4);
		$I->fillField('login_mobile_phone', $mobile_number);
		$I->click('weiter', '.step.step2');

		// tick all the check boxes

		$I->waitForElementVisible('#joinform .step.step3', 4);
		$I->checkOption('input[name=join_legal1]');
		$I->checkOption('input[name=join_legal2]');
		$I->click('Anmeldung absenden', '.step.step3');

		// we are signed up!

		$I->waitForElementVisible('#joinready', 4);
		$I->see('Deine Anmeldung war erfolgreich.');

		$I->expectNumMails(1, 4);

		// now login as that user

		$I->amOnPage('/');

		$I->fillField('email_adress', $this->email);
		$I->fillField('password', $this->password);
		$I->click('#loginbar input[type=submit]');

		$I->seeInDatabase('fs_foodsaver', [
			'email' => $this->stripped_email,
			'name' => $this->first_name,
			'nachname' => $this->last_name,
			'geb_datum' => $this->birthdate,
			'newsletter' => 0
		]);

		$I->waitForText('Um die foodsharing-Plattform benutzen zu können, musst Du die beschriebenenen Datenschutzerklärung zur Kenntnis nehmen. Es steht Dir frei, Deinen Account zu löschen.');
		$I->checkOption('#legal_form_privacy_policy');
		$I->click('Einstellungen übernehmen');
		$I->waitForText('Willkommen ' . $this->first_name . '!');

		$I->seeInDatabase('fs_foodsaver', [
			'email' => $this->stripped_email,
			'name' => $this->first_name,
			'nachname' => $this->last_name,
			'handy' => $mobile_number,
			'geb_datum' => $this->birthdate,
			'newsletter' => 0
		]);
	}
}
