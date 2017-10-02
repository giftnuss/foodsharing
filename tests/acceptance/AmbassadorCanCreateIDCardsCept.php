<?php

function convertRegionName($name) {
	$name = strtolower($name);

	$name = str_replace(array('ä', 'ö', 'ü', 'ß'), array('ae', 'oe', 'ue', 'ss'), $name);
	$name = preg_replace('/[^a-zA-Z]/', '', $name);
	return $name;
}

$testRegion = 241;
$I = new AcceptanceTester($scenario);
$I->wantTo('create an id card for a foodsaver');

$regionName = $I->grabFromDatabase('fs_bezirk', 'name', ['id' => $testRegion]);
$foodsaver = $I->createFoodsaver(null, ['name' => 'fs1', 'nachname' => 'saver1', 'photo' => 'does-not-exist.jpg']);
$ambassador = $I->createAmbassador(null, ['photo' => 'does-not-exist.jpg']);
$I->addBezirkMember($testRegion, $ambassador['id'], true);
$I->addBezirkMember($testRegion, $foodsaver['id']);

$I->login($ambassador['email']);

$I->amOnPage('/?page=passgen&bid=' . $testRegion);
$I->see('fs1 saver1');
$I->click('Alle markieren');
$I->wait(1);
$I->click('markierte Ausweise generieren');

$I->waitForPageBody();
$I->seeCurrentUrlEquals('/?page=passgen&bid='.$testRegion.'&dl1');
$I->dontSee('noch nicht erstellt');

$I->wait(4);

$I->seeFileExists('/downloads/foodsaver_pass_' . convertRegionName($regionName ). '.pdf');
