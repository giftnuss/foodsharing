<?php

function convertId($text) {
	$text = strtolower($text);
	str_replace(
		array('ä', 'ö', 'ü', 'ß', ' '),
		array('ae', 'oe', 'ue', 'ss', '_'),
		$text
	);
	return preg_replace('/[^a-z0-9_]/', '', $text);
}

$testRegion = 241;
$I = new AcceptanceTester($scenario);
$I->wantTo('create a business card as a foodsaver');

$regionName = $I->grabFromDatabase('fs_bezirk', 'name', ['id' => $testRegion]);
$foodsaver = $I->createFoodsaver(null, ['name' => 'fs1', 'nachname' => 'saver1', 'photo' => 'does-not-exist.jpg', 'handy' => '+4966669999']);
$I->addBezirkMember($testRegion, $foodsaver['id']);

$I->login($foodsaver['email']);

$I->amOnPage('/?page=bcard');
$I->selectOption('Optionen', 'Foodsaver für '. $regionName);
$I->waitForElementVisible('#pulse-info');
$I->click('Visitenkarten herunterladen');

$I->wait(4);

$I->seeFileExists('/downloads/bcard-fs-'. convertId($foodsaver['name']).'-'.convertId($foodsaver['nachname']).'.pdf');
