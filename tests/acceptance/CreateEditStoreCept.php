<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('create a store and manage it and my team');

$region = $I->createRegion('A region I test with', null, \Foodsharing\Modules\Core\DBConstants\Region\Type::CITY);
$storeName = 'Multistore 24';
$newStoreName = 'Ex-Ultrastore';
$storeStreet = 'Kantstraße 20';
$storePostcode = '04808';
$storeCity = 'Wurzen';

$extra_params = ['bezirk_id' => $region['id']];
$bibA = $I->createStoreCoordinator(null, $extra_params);
$foodsaverA = $I->createFoodsaver(null, $extra_params);
$foodsaverB = $I->createFoodsaver(null, $extra_params);
$bibB = $I->createStoreCoordinator(null, $extra_params);
$bibC = $I->createStoreCoordinator(null, $extra_params);

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
$I->fillField('#anschrift', $storeStreet);
$I->fillField('#plz', $storePostcode);
$I->fillField('#ort', $storeCity);

$I->fillField('#first_post', 'A first wallpost entry on the store');

$I->click('Senden');

/* See my mobile number because I am responsible */
$I->waitForText($storeStreet, null, '#inputAdress');
$I->waitForText($storePostcode, null, '#inputAdress');
$I->waitForText($storeCity, null, '#inputAdress');
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
$I->waitForElement('.tagedit-list', 5);
$I->addInTagSelect($bibB['name'], '#foodsaver');
$I->addInTagSelect($bibC['id'], '#foodsaver');
$I->addInTagSelect($foodsaverA['name'], '#foodsaver');
$I->addInTagSelect($foodsaverB['name'], '#foodsaver');
$I->click('Speichern', '#team-form');
$I->waitForElementNotVisible('#team-form', 5);

/* Mark another coordinator */
$I->click('Team bearbeiten');
$I->checkOption($bibB['name']);
$I->checkOption($bibC['nachname']);
$I->click('Speichern', '#team-form');

/* Edit the store to see that team does not change */
$I->amOnPage($I->storeEditUrl($storeId));
$I->click('Senden');
$I->see('Änderungen wurden gespeichert');

/* Reload to get rid of green overlay */
$I->amOnPage($I->storeUrl($storeId));

$I->see($bibA['name'] . ' ' . $bibA['nachname'], '.store-team');
$I->waitForElement('.store-team tr.table-warning[data-pk="' . $bibA['id'] . '"]', 5);
$I->see($bibB['handy'], '.store-team');
$I->waitForElement('.store-team tr.table-warning[data-pk="' . $bibB['id'] . '"]', 5);
$I->see($bibC['name'] . ' ' . $bibC['nachname'], '.store-team');
$I->waitForElement('.store-team tr.table-warning[data-pk="' . $bibC['id'] . '"]', 5);
$I->see($foodsaverA['name'] . ' ' . $foodsaverA['nachname'], '.store-team');
$I->see($foodsaverB['name'] . ' ' . $foodsaverB['nachname'], '.store-team');

$I->click('Team bearbeiten');
$I->removeFromTagSelect($bibC['name'] . ' ' . $bibC['nachname']);
$I->uncheckOption($bibB['name']);
$I->click('Speichern', '#team-form');
$I->see('Änderungen wurden gespeichert.');

/* Reload to get rid of green overlay */
$I->amOnPage($I->storeUrl($storeId));

$I->waitForElement('.store-team tr.table-warning[data-pk="' . $bibA['id'] . '"]', 5);
/* Make sure the demoted member is no longer displayed as store manager */
$I->waitForElement('.store-team tr[data-pk="' . $bibB['id'] . '"]:not(.table-warning)', 5);
/* Make sure the removed member is not displayed in the team at all */
$I->dontSee($bibC['name'] . ' ' . $bibC['nachname'], '.store-team');
$I->see($foodsaverA['name'] . ' ' . $foodsaverA['nachname'], '.store-team');
$I->see($foodsaverB['name'] . ' ' . $foodsaverB['nachname'], '.store-team');

$teamConversationMembers = $I->grabColumnFromDatabase('fs_foodsaver_has_conversation', 'foodsaver_id', ['conversation_id' => $teamConversationId]);
$jumperConversationMembers = $I->grabColumnFromDatabase('fs_foodsaver_has_conversation', 'foodsaver_id', ['conversation_id' => $jumperConversationId]);
$storeTeam = array_column([$bibA, $bibB, $foodsaverA, $foodsaverB], 'id');

$I->assertEquals($storeTeam, $I->grabColumnFromDatabase('fs_betrieb_team', 'foodsaver_id', ['betrieb_id' => $storeId, 'active' => 1]));
$I->assertEquals($storeTeam, $teamConversationMembers);

/* TODO fails, please fix: removed/demoted store managers stay in jumper chat
 * See https://gitlab.com/foodsharing-dev/-/issues/104 :)
 *
 * $storeManagers = array_column([$bibA], 'id');
 * $I->assertEquals($storeManagers, $jumperConversationMembers);
 */
