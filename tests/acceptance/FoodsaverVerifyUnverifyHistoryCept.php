<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('Verify, unverify and see verification history of a foodsaver');

$region = $I->createRegion();
$foodsaver = $I->createFoodsaver(null, ['bezirk_id' => $region['id']]);
$ambassador = $I->createAmbassador(null, ['name' => 'Bot', 'bezirk_id' => $region['id']]);
$I->addRegionAdmin($region['id'], $ambassador['id']);

/* define xpath locator
1. //a[contains(text(), "%s")]     look a the link with the foodsavers' name:
2. /ancestor::tr                   go up in the hierarchy and find all tr elements (should only be one)
3. //*[contains(...)]			   go down again and find the element that has the given class
*/
$verify_xpath = '//a[contains(text(), "%s")]/ancestor::tr//*[contains(concat(" ", normalize-space(@class), " "), " %s ")]';
$verify_undo = ['xpath' => sprintf($verify_xpath, $foodsaver['name'] . ' ' . $foodsaver['nachname'], 'verify-undo')];
$verify_do = ['xpath' => sprintf($verify_xpath, $foodsaver['name'] . ' ' . $foodsaver['nachname'], 'verify-do')];

$I->login($ambassador['email']);

$I->amOnPage('/profile/' . $foodsaver['id']);
$I->click('Verifizierungshistorie');
$I->waitForText('Es liegen keine Daten vor');

$I->amOnPage('/?page=passgen&bid=' . $region['id']);
$I->click($verify_undo);
$I->seeElement($verify_do);

$I->amOnPage('/?page=passgen&bid=' . $region['id']);
$I->click($verify_do);
$I->waitForText('Ausweis übergeben?');
$I->click('Verifizieren');
$I->seeElement($verify_undo);

$I->amOnPage('/profile/' . $foodsaver['id']);
$I->click('Verifizierungshistorie');

$I->waitForElement('.history .verify');
$I->seeElement('.history .unverify');
