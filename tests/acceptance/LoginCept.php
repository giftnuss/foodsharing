<?php

$I = new AcceptanceTester($scenario);

$pass = sq('pass');

$foodsaver = $I->createFoodsharer($pass);

$I->wantTo('ensure you can login');
$I->amOnPage('/');
$I->fillField('email_adress', $foodsaver['email']);
$I->fillField('password', $pass);
$I->click('#loginbar input[type=submit]');
$I->waitForText('Willkommen ' . $foodsaver['name'] . '!');
