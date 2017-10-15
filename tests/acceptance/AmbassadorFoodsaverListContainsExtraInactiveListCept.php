<?php

$testRegion = 241;
$I = new AcceptanceTester($scenario);
$I->wantTo('see that the foodsaver list for a bezirk contains a second list with inactive foodsavers');

$regionName = $I->grabFromDatabase('fs_bezirk', 'name', ['id' => $testRegion]);
$foodsaver = $I->createFoodsaver(null, ['name' => 'fs1', 'nachname' => 'saver1', 'photo' => 'does-not-exist.jpg']);
$inactiveFoodsaver = $I->createFoodsaver(null, ['name' => 'fs-i', 'nachname' => 'saver2', 'photo' => 'does-not-exist.jpg', 'last_login' => '2017-01-01 00:00:00']);
$activeFoodsaver =  $I->createFoodsaver(null, ['name' => 'fs-a', 'nachname' => 'saver3', 'photo' => 'does-not-exist.jpg', 'last_login' => (new \DateTime())->format('Y-m-d H:i:s')]);
$ambassador = $I->createAmbassador(null, ['photo' => 'does-not-exist.jpg']);
$I->addBezirkMember($testRegion, $ambassador['id'], true);
$I->addBezirkMember($testRegion, $foodsaver['id']);
$I->addBezirkMember($testRegion, $activeFoodsaver['id']);
$I->addBezirkMember($testRegion, $inactiveFoodsaver['id']);

$I->login($ambassador['email']);

$I->amOnPage('/?page=foodsaver&bid=' . $testRegion);
$I->see('Foodsaver in Göttingen', '#foodsaverlist');
$I->see('fs-a', '#foodsaverlist');
$I->see('fs-i', '#inactivefoodsaverlist');
$I->see('Inaktive Foodsaver', '#inactivefoodsaverlist');
$I->see('fs-i', '#inactivefoodsaverlist');
// That foodsaver never logged in, so is inactive as well. Actually this situation is hard to happen in real life as a not-logged-in user cannot have a region...
$I->see('fs1', '#inactivefoodsaverlist');
$I->dontSee('fs-a', '#inactivefoodsaverlist');


$I->wait(2);

