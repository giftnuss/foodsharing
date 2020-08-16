<?php

function convertRegionName($name)
{
	$name = strtolower($name);

	$name = str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $name);
	$name = preg_replace('/[^a-zA-Z]/', '', $name);

	return $name;
}

$I = new AcceptanceTester($scenario);
$I->wantTo('create an id card for a foodsaver');

$region = $I->createRegion();
$I->createFoodsaver(null, ['name' => 'fs1', 'nachname' => 'saver1', 'photo' => 'does-not-exist.jpg', 'bezirk_id' => $region['id']]);
$ambassador = $I->createAmbassador(null, ['photo' => 'does-not-exist.jpg', 'bezirk_id' => $region['id']]);
$I->addRegionAdmin($region['id'], $ambassador['id']);

$I->login($ambassador['email']);

$I->amOnPage('/?page=passgen&bid=' . $region['id']);
$I->waitForText('fs1 saver1');
$I->click('Alle markieren');
$I->click('Markierte Ausweise generieren');

/* ToDo: Not supported in new ci run style */
//$I->waitForFileExists('/downloads/foodsaver_pass_' . convertRegionName($region['name']) . '.pdf', 10);
