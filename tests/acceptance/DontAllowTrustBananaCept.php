<?php
$I = new AcceptanceTester($scenario);

// http://codeception.com/docs/modules/WebDriver


$I->wantTo('Check if people can give self banana');
$I->amOnPage('/');
$I->fillField('email_adress', 'userB@example.com');
$I->fillField('password', 'userb');
$I->click('#loginbar input[type=submit]');
$I->amOnPage('/?page=dashboard');
$I->see('Hallo');
$I->amOnPage('/profile/119684');
$I->see('Status updates von User');

/*
$I->waitForElementVisible('#bananas', 4);
$I->click('#bananas');
$I->dontSee('Schenke User eine Banane');
*/