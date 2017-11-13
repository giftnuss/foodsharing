<?php

$testRegion = 253;
$I = new AcceptanceTester($scenario);
$I->wantTo('Verify, unverify and see verification history of a foodsaver');

$foodsaver = $I->createFoodsaver(null, ['name' => 'a']);
$ambassador = $I->createAmbassador(null, ['name' => 'b']);
$I->addBezirkMember($testRegion, $ambassador['id'], true);
$I->addBezirkMember($testRegion, $foodsaver['id']);

$I->login($ambassador['email']);

$I->amOnPage('/profile/' . $foodsaver['id']);
$I->click('Verifizierungshistorie');
$I->waitForText('Es liegen keine Daten vor');

$I->amOnPage('/?page=passgen&bid=' . $testRegion);
$I->seeElement('.verify-y');

$I->clickWithLeftButton('.verify-y');
$I->waitForElementVisible('.verify-n');
$I->wait(1);
$I->amOnPage('/?page=passgen&bid=' . $testRegion);
$I->seeElement('.verify-n');

$I->clickWithLeftButton('.verify-n');
$I->waitForText('Ausweis übergeben?');
$I->click('Verifizieren');
$I->waitForElementVisible('.verify-y');

$I->amOnPage('/profile/' . $foodsaver['id']);
$I->click('Verifizierungshistorie');
$I->wait(1);

$I->waitForElementVisible('.history .verify');
$I->seeElement('.history .unverify');
