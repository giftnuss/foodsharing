<?php

function convertRegionName($name)
{
	$name = strtolower($name);

	$name = str_replace(array('ä', 'ö', 'ü', 'ß'), array('ae', 'oe', 'ue', 'ss'), $name);
	$name = preg_replace('/[^a-zA-Z]/', '', $name);

	return $name;
}

$testRegion = 241;
$I = new AcceptanceTester($scenario);
$I->wantTo('create an id card for a foodsaver');

$regionName = $I->grabFromDatabase('fs_bezirk', 'name', ['id' => $testRegion]);
$foodsaver = $I->createFoodsaver(null, ['name' => 'fs1', 'nachname' => 'saver1', 'photo' => 'does-not-exist.jpg', 'bezirk_id' => $testRegion]);
$ambassador = $I->createAmbassador(null, ['photo' => 'does-not-exist.jpg', 'bezirk_id' => $testRegion]);
$I->addBezirkAdmin($testRegion, $ambassador['id']);

$I->login($ambassador['email']);

$I->amOnPage('/?page=passgen&bid=' . $testRegion);
$I->see('fs1 saver1');
$I->click('Alle markieren');
$I->click('markierte Ausweise generieren');

$I->waitForFileExists('/downloads/foodsaver_pass_' . convertRegionName($regionName) . '.pdf', 10);
