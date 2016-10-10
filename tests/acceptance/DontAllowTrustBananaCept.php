<?php
$I = new AcceptanceTester($scenario);

// http://codeception.com/docs/modules/WebDriver


$I->wantTo('Check if people can give self banana');
$I->amOnPage('/');
$I->fillField('email_adress', 'userB@example.com');
$I->fillField('password', 'userb');
$I->click('#loginbar input[type=submit]');
$I->seeCurrentUrlEquals('/?page=dashboard'); // it redirects
$I->see('Hallo');
$I->amOnPage('/profile/119684');
$I->see('Statusupdates von User');

$I->waitForElementVisible('a.item.stat_bananacount.bouched', 4);
$I->click('a.item.stat_bananacount.bouched');
$I->dontSee('Schenke User eine Banane');
