<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('delete my account (being a foodsaver)');

$pass = sq('pass');

$foodsaver = $I->createFoodsaver($pass);

$I->login($foodsaver['email'], $pass);

$I->amOnPage('/?page=settings&sub=deleteaccount');

$I->click('#delete-account');

$I->waitForElementVisible('#delete-account-confirm', 4);
$I->click('Ja, Account jetzt');
$I->waitForPageBody();

$I->seeInDatabase('fs_foodsaver', [
	'id' => $foodsaver['id'],
	'name' => NULL,
	'email' => NULL,
	'nachname' => NULL
]);

$I->seeInDatabase('fs_foodsaver_archive', [
	'id' => $foodsaver['id'],
	'name' => $foodsaver['name'],
	'email' => $foodsaver['email'],
	'nachname' => $foodsaver['nachname']
]);
