<?php

$I = new AcceptanceTester($scenario);

$pass = sq('pass');

$foodsaver = $I->createFoodsharer($pass);

$I->wantTo('ensure you can login');
$I->amOnPage('/');
$I->fillField('login_form[email_address]', $foodsaver['email']);
$I->fillField('login_form[password]', $pass);
$I->click('#loginbar input[type=submit]');
$I->waitForText('Willkommen ' . $foodsaver['name'] . '!');
