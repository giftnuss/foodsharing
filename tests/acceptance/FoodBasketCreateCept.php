<?php

$I = new AcceptanceTester($scenario);

$description = sq('yay');
$pass = sq('pass');

$foodsaver = $I->createFoodsaver($pass);

$I->wantTo('Ensure I can create a food basket');

$I->login($foodsaver['email'], $pass);

$I->amOnPage('/');

$I->click('#infobar .basket a');
$I->see('Neuen Essenskorb anlegen');

$I->click('Neuen Essenskorb anlegen');
$I->waitForText('Essenskorb anbieten');
$I->canSeeCheckboxIsChecked('.input.cb-contact_type[value="1"]');
$I->cantSeeCheckboxIsChecked('.input.cb-contact_type[value="2"]');

$I->fillField('description', $description);

$I->click('Essenskorb veröffentlichen');

$I->waitForElementVisible('#pulse-info', 4);
$I->see('Danke Dir! Der Essenskorb wurde veröffentlicht!');

$I->seeInDatabase('fs_basket', [
	'description' => $description,
	'foodsaver_id' => $foodsaver['id']
]);
