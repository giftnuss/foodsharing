<?php

$I = new AcceptanceTester($scenario);

$pass = sq('pass');

$foodsaver = $I->createFoodsharer($pass);

$I->wantTo('ensure you can login');
$I->amOnPage('/');
$I->click('#login');
$I->waitForElement('#login-email');
$I->fillField('#login-email', $foodsaver['email']);
$I->fillField('#login-password', $pass);
$I->click('#login-btn');
$I->waitForActiveAPICalls();
$I->waitForElementNotVisible('#pulse-success');
$I->waitForPageBody();
$I->waitForText('Hallo ' . $foodsaver['name'] . '!');
