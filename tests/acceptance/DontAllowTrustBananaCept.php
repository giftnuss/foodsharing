<?php
$I = new AcceptanceTester($scenario);

// http://codeception.com/docs/modules/WebDriver


$I->wantTo('Check if people can give self banana');
$I->amOnPage('/');
$I->fillField('email_adress', 'userB@example.com');
$I->fillField('password', 'userb');
$I->click('#loginbar input[type=submit]');
$I->seeCurrentUrlEquals('/?page=dashboard'); // it redirects
$I->waitForPageBody();
$I->see('Hallo');

$I->amOnPage('/profile/119684');
$I->see('Statusupdates von User');

$I->waitForElementVisible('a.item.stat_bananacount.bouched', 4);
$I->click('a.item.stat_bananacount.bouched');
// This might need a wait as well but it would be a bit harder to figure out.
// if this test ever fails here, rerun & think about a fix!
$I->dontSee('Schenke User eine Banane');
