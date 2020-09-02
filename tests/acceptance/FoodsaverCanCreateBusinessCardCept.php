<?php

function convertId($text)
{
	$text = strtolower($text);
	str_replace(
		['ä', 'ö', 'ü', 'ß', ' '],
		['ae', 'oe', 'ue', 'ss', '_'],
		$text
	);

	return preg_replace('/[^a-z0-9_]/', '', $text);
}

$I = new AcceptanceTester($scenario);
$I->wantTo('create a business card as a foodsaver');

$region = $I->createRegion();

$foodsaver = $I->createFoodsaver(null, ['name' => 'fs1', 'nachname' => 'saver1', 'photo' => 'does-not-exist.jpg', 'handy' => '+4966669999', 'bezirk_id' => $region['id']]);

$I->login($foodsaver['email']);

$I->amOnPage('/?page=bcard');
$I->selectOption('Optionen', 'Foodsaver*in für ' . $region['name']);

/* ToDo: Not supported in new CI run style */
//$I->waitForFileExists('/downloads/bcard-fs.pdf');
