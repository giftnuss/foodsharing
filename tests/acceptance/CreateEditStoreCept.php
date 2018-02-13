<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('create a store and manage it and my team');

$region = $I->createRegion('A region I test with');
$storeName = 'Multistore 24';
$newStoreName = 'Ex-Ultrastore';
$storeStreet = 'Kantstraße';
$storeHouseNumber = '20';
$storePostcode = '04808';
$storeCity = 'Wurzen';

$extra_params = ['bezirk_id' => $region['id']];
$bibA = $I->createStoreCoordinator(null, $extra_params);
$foodsaverA = $I->createFoodsaver(null, $extra_params);
$foodsaverB = $I->createFoodsaver(null, $extra_params);
$bibB = $I->createStoreCoordinator(null, $extra_params);

$I->login($bibA['email']);

$I->amOnPage($I->storeNewUrl());

$I->fillField('#name', $storeName);

/* This part would rely on geocoding to work. Apparantly, typeahead suggestions don't come up using fillField. Any ideas? */
/*
$I->fillField('#addresspicker', $storeAddress);
$I->canSee($storeAddress, '.tt-suggestion');
$I->click($storeAddress, '.tt-suggestion');
*/
$I->unlockAllInputFields();
$I->fillField('#str', $storeStreet);
$I->fillField('#hsnr', $storeHouseNumber);
$I->fillField('#plz', $storePostcode);
$I->fillField('#ort', $storeCity);

$I->fillField('#first_post', 'A first wallpost entry on the store');

$I->click('Senden');

/* See my mobile number because I am responsible */
$I->see($bibA['handy']);

$teamConversationId = $I->grabFromDatabase('fs_betrieb', 'team_conversation_id', ['name' => $storeName]);
$jumperConversationId = $I->grabFromDatabase('fs_betrieb', 'springer_conversation_id', ['name' => $storeName]);

$I->seeInDatabase('fs_foodsaver_has_conversation', ['conversation_id' => $teamConversationId, 'foodsaver_id' => $bibA['id']]);
$I->seeInDatabase('fs_foodsaver_has_conversation', ['conversation_id' => $jumperConversationId, 'foodsaver_id' => $bibA['id']]);

$storeId = $I->grabFromCurrentUrl('~&id=(\d+)~');

/* Rename the store */
$I->amOnPage($I->storeEditUrl($storeId));
$I->fillField('#name', $newStoreName);
$I->click('Senden');

$I->see($newStoreName . '-Team');
/* Reload to get rid of green overlay */
$I->amOnPage($I->storeUrl($storeId));

/* Add more Users */
$I->click('Team bearbeiten');
$I->addInTagSelect($bibB['name'], '#foodsaver');
$I->addInTagSelect($foodsaverA['name'], '#foodsaver');
$I->addInTagSelect($foodsaverB['name'], '#foodsaver');
$I->click('Speichern', '#team-form');

/* Mark another coordinator */
$I->click('Team bearbeiten');
$I->checkOption($bibB['name']);
$I->click('Speichern', '#team-form');

/* Edit the store to see that team does not change */
$I->amOnPage($I->storeEditUrl($storeId));
$I->click('Senden');
$I->see('Änderungen wurden gespeichert');

/* Reload to get rid of green overlay */
$I->amOnPage($I->storeUrl($storeId));

$I->see($bibA['name'] . ' ' . $bibA['nachname'], '.team .verantwortlich');
$I->see($bibB['name'] . ' ' . $bibB['nachname'], '.team .verantwortlich');
$I->see($foodsaverA['name'] . ' ' . $foodsaverA['nachname'], '.team');
$I->see($foodsaverB['name'] . ' ' . $foodsaverB['nachname'], '.team');

$I->click('Team bearbeiten');
$I->removeFromTagSelect($bibB['name'] . ' ' . $bibB['nachname']);
$I->click('Speichern', '#team-form');
$I->see('Änderungen wurden gespeichert.');

/* Reload to get rid of green overlay */
$I->amOnPage($I->storeUrl($storeId));

$I->see($bibA['name'] . ' ' . $bibA['nachname'], '.team .verantwortlich');
$I->dontSee($bibB['name'] . ' ' . $bibB['nachname'], '.team .verantwortlich');
$I->see($foodsaverA['name'] . ' ' . $foodsaverA['nachname'], '.team');
$I->see($foodsaverB['name'] . ' ' . $foodsaverB['nachname'], '.team');

$teamConversationMembers = $I->grabColumnFromDatabase('fs_foodsaver_has_conversation', 'foodsaver_id', ['conversation_id' => $teamConversationId]);
$jumperConversationMembers = $I->grabColumnFromDatabase('fs_foodsaver_has_conversation', 'foodsaver_id', ['conversation_id' => $jumperConversationId]);
$storeTeamIDs = [$bibA['id'], $foodsaverA['id'], $foodsaverB['id']];
$storeCoordinatorIDs = [$bibA['id']];
$I->assertEquals($storeTeamIDs, $teamConversationMembers);
/* TODO fails, please fix. See https://gitlab.com/foodsharing-dev/issues0/issues/352 :) */
/*
 * $I->assertEquals($storeCoordinatorIDs, $jumperConversationMembers);
 */
