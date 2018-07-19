<?php

$I = new AcceptanceTester($scenario);

$pass = sq('pass');

$foodsaver = $I->createFoodsharer($pass);

$I->wantTo('ensure you can login');
$I->amOnPage('/');
$I->waitForElement('#login-username');
$I->fillField('#login-username', $foodsaver['email']);
$I->fillField('#login-password', $pass);
$I->click('#topbar .btn');
$I->waitForElement('#pulse-success');
$I->waitForElementNotVisible('#pulse-success');
$I->waitForPageBody();
$I->waitForText('Willkommen ' . $foodsaver['name'] . '!');
