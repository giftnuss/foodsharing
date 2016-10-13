<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('Check if people can give self banana');
$pass = sq('pass');

$foodsaver = $I->createFoodsaver($pass, [
	'rolle' => 1,
	'admin' => 1
]);

$I->login($foodsaver['email'], $pass);

$I->amOnPage('/profile/'.$foodsaver['id']);
$I->see('Statusupdates von '.$foodsaver['name']);

$I->waitForElementVisible('a.item.stat_bananacount.bouched', 4);
$I->click('a.item.stat_bananacount.bouched');
// This might need a wait as well but it would be a bit harder to figure out.
// if this test ever fails here, rerun & think about a fix!
$I->dontSee('Schenke User eine Banane');