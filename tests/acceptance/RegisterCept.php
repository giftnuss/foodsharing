<?php

$I = new AcceptanceTester($scenario);

// http://codeception.com/docs/modules/WebDriver

// create some unique values for our new user
$email = sq('email') . '@test.com';
$first_name = sq('first_name');
$last_name = sq('last_name');
$password = sq('password');

$I->wantTo('ensure I can register');
$I->amOnPage('/');

// click signup, then press next on the first dialog

$I->click('Mach mit!');
$I->waitForElementVisible('#joinform', 4);
$I->waitForElementVisible('#joinform .step.step0', 4);
$I->click('weiter', '.step.step0');

// fill in basic details

$I->waitForElementVisible('#joinform .step.step1', 4);
$I->fillField('login_name', $first_name);
$I->fillField('login_surname', $last_name);
$I->fillField('login_email', $email);
$I->fillField('#login_passwd1', $password);
$I->fillField('#login_passwd2', $password);
$I->click('weiter', '.step.step1');

// it gives us an alert to complain we did not upload a photo
// whatever, I'm in a hurry

$I->seeInPopup('Du hast kein Foto hochgeladen.');
$I->acceptPopup();

// skip the step with the address map, it is optional

$I->waitForElementVisible('#joinform .step.step2', 4);
$I->click('weiter', '.step.step2');

// tick all the check boxes

$I->waitForElementVisible('#joinform .step.step3', 4);
$I->checkOption('input[name=join_legal1]');
$I->checkOption('input[name=join_legal2]');
$I->checkOption('input[name=newsletter]');
$I->click('Anmeldung absenden', '.step.step3');

// we are signed up!

$I->waitForElementVisible('#joinready', 4);
$I->see('Deine Anmeldung war erfolgreich!');

$I->expectNumMails(1, 1);

// now login as that user

$I->amOnPage('/');

$I->fillField('email_adress', $email);
$I->fillField('password', $password);
$I->click('#loginbar input[type=submit]');
$I->waitForPageBody();
$I->see('Willkommen ' . $first_name . '!');

$I->seeInDatabase('fs_foodsaver', [
	'email' => $email,
	'name' => $first_name,
	'nachname' => $last_name
]);
