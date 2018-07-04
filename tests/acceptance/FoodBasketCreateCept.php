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
$I->seeCheckboxIsChecked('.input.cb-contact_type[value="1"]');
$I->dontSeeCheckboxIsChecked('.input.cb-contact_type[value="2"]');
$I->seeOptionIsSelected('#weight', '3,0');
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

$id = $I->grabFromDatabase('fs_basket', 'id', ['description' => $description,
	'foodsaver_id' => $foodsaver['id']]);

$picker = $I->createFoodsaver();

$nick = $I->haveFriend('nick');
$nick->does(function (AcceptanceTester $I) use ($id, $picker) {
	$I->login($picker['email']);
	$I->amOnPage($I->foodBasketInfoUrl($id));

	$I->waitForText('Essenskorb anfragen');
	$I->click('Essenskorb anfragen');
	$I->waitForText('Anfrage absenden');
	$I->fillField('#contactmessage', 'Hi friend, can I have the basket please?');
	$I->click('Anfrage absenden');

	$I->waitForText('Anfrage wurde versendet');
});

$I->amOnPage($I->foodBasketInfoUrl($id));
$I->waitForText('1 Anfrage');
$I->click('.img-fbasket');
$I->waitForText('Anfrage von ' . $picker['name']);
$I->click('Anfrage von ' . $picker['name']);
$I->waitForText('Hi friend, can I have');
$I->moveMouseOver("//a[contains(@onclick, 'basket')]");
$I->click('.button.close');
$I->waitForText('Essenskorbanfrage von ' . $picker['name'] . ' abschließen');
$I->see('Hat alles gut geklappt?');
$I->seeOptionIsSelected('#fetchstate-wrapper input[name=fetchstate]', 2);
$I->click('Weiter');
$I->waitForText('Danke');
