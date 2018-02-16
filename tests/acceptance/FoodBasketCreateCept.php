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
/*
 * Check for default options on the foodbasket create form.
 * this was implemented mainly to check the v_components when refactoring default options.
 */
$I->canSeeCheckboxIsChecked('.input.cb-contact_type[value="1"]');
$I->cantSeeCheckboxIsChecked('.input.cb-contact_type[value="2"]');
$I->canSeeOptionIsSelected('#weight', '3,0');
$I->dontSeeElement('#handy');
$I->checkOption('.input.cb-contact_type[value="2"]');
$I->waitForElement('#handy');
$I->seeInField('#handy', $foodsaver['handy']);

$I->fillField('description', $description);

/* This line should not be necessary - actually the window should not get too big! */
$I->scrollTo('//*[contains(text(),"Essenskorb veröffentlichen")]');
$I->click('Essenskorb veröffentlichen');

$I->waitForElementVisible('#pulse-info', 4);
$I->see('Danke Dir! Der Essenskorb wurde veröffentlicht!');

$I->seeInDatabase('fs_basket', [
	'description' => $description,
	'foodsaver_id' => $foodsaver['id']
]);
