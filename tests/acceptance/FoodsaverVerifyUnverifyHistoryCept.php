<?php

$testRegion = 253;
$I = new AcceptanceTester($scenario);
$I->wantTo('Verify, unverify and see verification history of a foodsaver');

$foodsaver = $I->createFoodsaver(null, ['bezirk_id' => $testRegion]);
$ambassador = $I->createAmbassador(null, ['name' => 'Bot', 'bezirk_id' => $testRegion]);
$I->addBezirkAdmin($testRegion, $ambassador['id']);

/* define xpath locator
1. //a[contains(text(), "%s")]     look a the link with the foodsavers' name:
2. /ancestor::tr                   go up in the hierarchy and find all tr elements (should only be one)
3. //*[contains(...)]			   go down again and find the element that has the given class
*/
$verify_xpath = '//a[contains(text(), "%s")]/ancestor::tr//*[contains(concat(" ", normalize-space(@class), " "), " %s ")]';
$verify_y = ['xpath' => sprintf($verify_xpath, $foodsaver['nachname'], 'verify-y')];
$verify_n = ['xpath' => sprintf($verify_xpath, $foodsaver['nachname'], 'verify-n')];

$I->login($ambassador['email']);

$I->amOnPage('/profile/' . $foodsaver['id']);
$I->click('Verifizierungshistorie');
$I->waitForText('Es liegen keine Daten vor');

$I->amOnPage('/?page=passgen&bid=' . $testRegion);
$I->click($verify_y);
$I->seeElement($verify_n);

$I->amOnPage('/?page=passgen&bid=' . $testRegion);
$I->click($verify_n);
$I->waitForText('Ausweis übergeben?');
$I->click('Verifizieren');
$I->seeElement($verify_y);

$I->amOnPage('/profile/' . $foodsaver['id']);
$I->click('Verifizierungshistorie');

$I->waitForElement('.history .verify');
$I->seeElement('.history .unverify');
