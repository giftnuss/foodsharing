<?php

class RegisterCest
{
	private $email;
	private $stripped_email;
	private $first_name;
	private $last_name;
	private $password;
	private $birthdate;
	private $mobile_number;

	public function _before()
	{
		$this->email = '     ' . sq('email') . '@test.com      ';
		$this->stripped_email = sq('email') . '@test.com';
		$this->first_name = sq('first_name');
		$this->last_name = sq('last_name');
		$this->password = sq('password');
		$this->birthdate = '1983-04-27';
		$this->mobile_number = '+491773231323';
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
		$I->waitForElementVisible('#step1', 4);
		$I->fillField('#email', $this->email);
		$I->fillField('#password', $this->password);
		$I->fillField('#confirmPassword', $this->password);
		$I->click('weiter');

		// fill in basic details

		$I->waitForElementVisible('#step2', 4);
		$I->click('label[for="genderWoman"]');
		$I->fillField('#firstname', $this->first_name);
		$I->fillField('#lastname', $this->last_name);
		$I->click('weiter');

		$I->waitForElementVisible('#step3', 4);
		$I->fillField('input[class=form-control]', $this->birthdate);
		$I->click('weiter');

		$I->waitForElementVisible('#step4', 4);
		$I->fillField('#form4.mobile', $this->mobile_number);
		$I->click('weiter');

		// tick all the check boxes

		$I->waitForElementVisible('#step5', 4);
		$I->checkOption('#form5.join_legal1');
		$I->checkOption('#form5.join_legal2');
		$I->seeElement('#step5 > div > div.custom-control.custom-checkbox > label');
		$I->seeCheckboxIsChecked('#form5.subscribeNewsletter');
		$I->click('#step5 > div > div.custom-control.custom-checkbox > label');
		$I->dontSeeCheckboxIsChecked('#form5.subscribeNewsletter');
		$I->click('Anmeldung absenden');

		// we are signed up!
		$I->waitForElementVisible('#step6', 4);
		$I->see('Du hast die Anmeldung bei foodsharing erfolgreich abgeschlossen.');

		$I->expectNumMails(1, 4);

		// now login as that user

		$I->amOnPage('/');

		$I->waitForElement('#login-email');
		$I->fillField('#login-email', $this->email);
		$I->fillField('#login-password', $this->password);
		$I->click('#topbar .btn');
		$I->waitForElement('#pulse-success');

		$I->seeInDatabase('fs_foodsaver', [
			'email' => $this->stripped_email,
			'name' => $this->first_name,
			'nachname' => $this->last_name,
			'geb_datum' => $this->birthdate,
			'newsletter' => 0,
			'handy' => $this->mobile_number
		]);

		$I->waitForText('Um die foodsharing-Plattform benutzen zu können, musst Du die beschriebenenen Datenschutzerklärung zur Kenntnis nehmen. Es steht Dir frei, Deinen Account zu löschen.');
		$I->checkOption('#legal_form_privacy_policy');
		$I->click('Einstellungen übernehmen');
		$I->waitForText('Willkommen ' . $this->first_name . '!');

		$I->seeInDatabase('fs_foodsaver', [
			'email' => $this->stripped_email,
			'name' => $this->first_name,
			'nachname' => $this->last_name,
			'handy' => $this->mobile_number,
			'geb_datum' => $this->birthdate,
			'newsletter' => 0
		]);
	}
}
