<?php

$testRegion = 241;
$I = new AcceptanceTester($scenario);
$I->wantTo('create an id card for a foodsaver');

$regionName = $I->grabFromDatabase('fs_bezirk', 'name', ['id' => $testRegion]);
$foodsaver = $I->createFoodsaver(null, ['name' => 'fs1', 'nachname' => 'saver1', 'photo' => 'does-not-exist.jpg']);
$ambassador = $I->createAmbassador(null, ['photo' => 'does-not-exist.jpg']);
$I->addBezirkMember($testRegion, $ambassador['id'], true);
$I->addBezirkMember($testRegion, $foodsaver['id']);

$I->login($ambassador['email']);

$I->amOnPage('/?page=foodsaver&bid=' . $testRegion);
$I->see('Foodsaver in Göttingen');
$I->see('Inaktive Foodsaver');

$I->wait(2);

