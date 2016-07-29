<?php
$I = new AcceptanceTester($scenario);

// http://codeception.com/docs/modules/WebDriver

// create some unique values for our new user
$email = 'test-'.round(microtime(true) * 1000).'@test.com';
$first_name = uniqid();
$last_name = uniqid();
$password = uniqid();

$I->wantTo('ensure I can register');
$I->amOnPage('/');

// disable popups, as they are not supported in PhantomJS
// if they were could use seeInPopup/acceptPopup
$I->executeJS("window.confirm = function(){return true;};");
$I->executeJS("window.alert = function(){return true;};");

// click signup, then press next on the first dialog

$I->click('Mach-Mit!');
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

// skip the step with the address map, it is optional

$I->waitForElementVisible('#joinform .step.step2', 4);
$I->click('weiter', '.step.step2');

// tick all the check boxes

$I->waitForElementVisible('#joinform .step.step3', 4);
$I->checkOption('input[name=join_legal1]');
$I->checkOption('input[name=join_legal2]');
$I->checkOption('input[name=newsletter]');
$I->click('Anmeldung absenden', '.step.step3');

// codecept_debug($I->grabTextFrom('#joinready'));

// we are signed up!

$I->waitForElementVisible('#joinready', 4);
$I->see('Deine Anmeldung war erfolgreich!');

// now login as that user

$I->fillField('email_adress', $email);
$I->fillField('password', $password);
$I->click('#loginbar input[type=submit]');
$I->see('Willkommen '.$first_name.'!');
