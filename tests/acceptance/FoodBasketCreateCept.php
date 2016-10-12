<?php
$I = new AcceptanceTester($scenario);

$description = sq('yay');
$pass = sq('pass');

$foodsaver = $I->createFoodsaver([
	'email' => sq('email').'@test.com',
	'bezirk_id' => 1, // Deutschland
	'name' => sq('name'),
	'nachname' => sq('nachname')
], $pass);

$I->wantTo('Ensure I can create a food basket');

$I->login($foodsaver['email'], $pass);

$I->amOnPage('/');

$I->click('#infobar .basket a');
$I->see('Neuen Essenskorb anlegen');

$I->click('Neuen Essenskorb anlegen');
$I->see('Essenskorb anbieten');

$I->fillField('description', $description);

$I->click('Essenskorb veröffentlichen');

$I->waitForElementVisible('#pulse-info', 4);
$I->see('Danke Dir! Der Essenskorb wurde veröffentlicht!');

$I->seeNumRecords(1, 'fs_basket', [
	'description' => $description,
	'foodsaver_id' => $foodsaver['id']
]);