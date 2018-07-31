<?php

$I = new AcceptanceTester($scenario);

$pass = sq('pass');

$foodsaver = $I->createFoodsharer($pass);

$I->wantTo('ensure you can login');
$I->amOnPage('/');
$I->waitForElement('#login-email');
$I->fillField('#login-email', $foodsaver['email']);
$I->fillField('#login-password', $pass);
$I->click('#topbar .btn');
$I->waitForActiveAPICalls();
$I->waitForElementNotVisible('#pulse-success');
$I->waitForPageBody();
$I->waitForText('Willkommen ' . $foodsaver['name'] . '!');
