<?php

function convertRegionName($name)
{
	$name = strtolower($name);

	$name = str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $name);
	$name = preg_replace('/[^a-zA-Z]/', '', $name);

	return $name;
}

$testRegionId = 241;
$I = new AcceptanceTester($scenario);
$I->wantTo('create an id card for a foodsaver');

$regionName = $I->grabFromDatabase('fs_bezirk', 'name', ['id' => $testRegionId]);
$I->createFoodsaver(null, ['name' => 'fs1', 'nachname' => 'saver1', 'photo' => 'does-not-exist.jpg', 'bezirk_id' => $testRegionId]);
$ambassador = $I->createAmbassador(null, ['photo' => 'does-not-exist.jpg', 'bezirk_id' => $testRegionId]);
$I->addRegionAdmin($testRegionId, $ambassador['id']);

$I->login($ambassador['email']);

$I->amOnPage('/?page=passgen&bid=' . $testRegionId);
$I->waitForText('fs1 saver1');
$I->click('Alle markieren');
$I->click('Markierte Ausweise generieren');

$I->waitForFileExists('/downloads/foodsaver_pass_' . convertRegionName($regionName) . '.pdf', 10);
