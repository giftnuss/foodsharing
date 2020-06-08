<?php

use Foodsharing\Modules\Core\DBConstants\StoreTeam\MembershipStatus;

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
$bibB = $I->createStoreCoordinator(null, $extra_params);
$bibC = $I->createStoreCoordinator(null, $extra_params);
$foodsaverD = $I->createFoodsaver(null, $extra_params);
$foodsaverE = $I->createFoodsaver(null, $extra_params);
$foodsaverF = $I->createFoodsaver(null, $extra_params);

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

$I->seeInDatabase('fs_foodsaver_has_conversation', [
	'conversation_id' => $teamConversationId,
	'foodsaver_id' => $bibA['id'],
]);
$I->seeInDatabase('fs_foodsaver_has_conversation', [
	'conversation_id' => $jumperConversationId,
	'foodsaver_id' => $bibA['id'],
]);

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
$I->addInTagSelect($foodsaverD['name'], '#foodsaver');
$I->addInTagSelect($foodsaverE['nachname'], '#foodsaver');
$I->addInTagSelect($foodsaverF['id'], '#foodsaver');
$I->click('Speichern', '#team-form');
$I->waitForElementNotVisible('#team-form', 5);

/* Promote two more store managers */
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

$I->waitForElement('.store-team tr.table-warning[data-pk="' . $bibA['id'] . '"]', 5);
$I->waitForElement('.store-team tr.table-warning[data-pk="' . $bibB['id'] . '"]', 5);
$I->waitForElement('.store-team tr.table-warning[data-pk="' . $bibC['id'] . '"]', 5);
$I->see($bibA['name'] . ' ' . $bibA['nachname'], '.store-team');
$I->see($bibB['handy'], '.store-team');
$I->see($bibC['name'] . ' ' . $bibC['nachname'], '.store-team');
$I->see($foodsaverD['name'] . ' ' . $foodsaverD['nachname'], '.store-team');
$I->see($foodsaverE['name'] . ' ' . $foodsaverE['nachname'], '.store-team');
$I->see($foodsaverF['name'] . ' ' . $foodsaverF['nachname'], '.store-team');

/* Remove one store manager from the team, demote another to regular team member */
$I->click('Team bearbeiten');
// TODO this is important!! and should be fixed to not be necessary
$I->uncheckOption($bibC['name'] . ' ' . $bibC['nachname']);
$I->removeFromTagSelect($bibC['name'] . ' ' . $bibC['nachname']);
$I->uncheckOption($bibB['name'] . ' ' . $bibB['nachname']);
$I->click('Speichern', '#team-form');
$I->see('Änderungen wurden gespeichert.');

/* Reload to get rid of green overlay */
$I->amOnPage($I->storeUrl($storeId));

$I->waitForElement('.store-team tr.table-warning[data-pk="' . $bibA['id'] . '"]', 5);
/* Make sure the demoted member is no longer displayed as store manager */
$I->waitForElement('.store-team tr[data-pk="' . $bibB['id'] . '"]:not(.table-warning)', 5);
/* Make sure the removed member is not displayed in the team at all */
$I->dontSee($bibC['name'] . ' ' . $bibC['nachname'], '.store-team');
$I->see($foodsaverD['name'] . ' ' . $foodsaverD['nachname'], '.store-team');
$I->see($foodsaverE['name'] . ' ' . $foodsaverE['nachname'], '.store-team');
$I->see($foodsaverF['name'] . ' ' . $foodsaverF['nachname'], '.store-team');

// convert a member to jumper (standby list)
$I->click($foodsaverD['name'] . ' ' . $foodsaverD['nachname'], '.store-team');
$I->click('Auf die Springerliste', '.member-actions');
// implicit assumption: clicking on this action button closes the .member-actions list
//
// check that the jumper is still displayed as team member (but with muted colors)
$I->see($foodsaverF['name'] . ' ' . $foodsaverF['nachname'], '.store-team');
$I->waitForElement('.store-team #member-' . $foodsaverD['id'] . '.member-info.jumper', 5);
$I->wait(2); // should become $I->waitForActiveAPICalls(); after RESTification

$jumperIds = $I->grabColumnFromDatabase('fs_betrieb_team', 'foodsaver_id', [
	'betrieb_id' => $storeId,
	'active' => MembershipStatus::JUMPER,
]);
$jumpers = array_column([$foodsaverD], 'id');
$I->assertEquals($jumperIds, $jumpers);

// remove a member from the team entirely
$I->click($foodsaverF['name'] . ' ' . $foodsaverF['nachname'], '.store-team');
$I->click('Aus dem Team entfernen', '.member-actions');
$I->seeInPopup('Bist Du sicher?');
$I->cancelPopup();
// confirm alert this time
$I->click('Aus dem Team entfernen', '.member-actions');
$I->seeInPopup('Bist Du sicher?');
$I->acceptPopup();
$I->dontSee($foodsaverF['name'] . ' ' . $foodsaverF['nachname'], '.store-team');
$I->wait(2); // should become $I->waitForActiveAPICalls(); after RESTification
$I->dontSeeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $foodsaverF['id'],
]);

$storeTeam = array_column([$bibA, $bibB, $foodsaverE], 'id');
$I->assertEquals($storeTeam, $I->grabColumnFromDatabase('fs_betrieb_team', 'foodsaver_id', [
	'betrieb_id' => $storeId,
	'active' => MembershipStatus::MEMBER,
]));

$teamConversationMembers = $I->grabColumnFromDatabase('fs_foodsaver_has_conversation', 'foodsaver_id', [
	'conversation_id' => $teamConversationId,
]);
$I->assertEquals($storeTeam, $teamConversationMembers);

$storeManagers = array_column([$bibA], 'id');
$I->assertEquals($storeManagers, $I->grabColumnFromDatabase('fs_betrieb_team', 'foodsaver_id', [
	'betrieb_id' => $storeId,
	'active' => MembershipStatus::MEMBER,
	'verantwortlich' => 1,
]));

// There were bugs with removed/demoted store managers staying in jumper chat
// See https://gitlab.com/foodsharing-dev/-/issues/104 for details :)
$jumperConversationMembers = $I->grabColumnFromDatabase('fs_foodsaver_has_conversation', 'foodsaver_id', [
	'conversation_id' => $jumperConversationId,
]);
$I->assertEquals($jumperConversationMembers, array_merge($storeManagers, $jumperIds));
