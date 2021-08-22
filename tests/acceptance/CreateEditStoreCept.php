<?php

use Foodsharing\Modules\Core\DBConstants\StoreTeam\MembershipStatus;

/**
 * Uses the search field in the store management panel to add a user to the team.
 */
function addToTeam(AcceptanceTester $I, array $user)
{
	$I->fillField('#new-foodsaver-search input', $user['name']);
	$I->waitForActiveAPICalls();
	$I->waitForElement('#new-foodsaver-search li.suggest-item');
	$I->click('#new-foodsaver-search li.suggest-item');
	$I->click('#new-foodsaver-search button[type="submit"]');
	$I->waitForActiveAPICalls();
}

$I = new AcceptanceTester($scenario);

$I->wantTo('create a store and manage it and my team');

$region = $I->createRegion();
$storeName = 'Multistore 24';
$newStoreName = 'Ex-Ultrastore';
$storeStreet = 'Kantstraße 20';
$storePostcode = '04808';
$storeCity = 'Wurzen';

$extra_params = ['bezirk_id' => $region['id']];
$bibA = $I->createStoreCoordinator(null, $extra_params);
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
$I->click('Ansicht für Betriebsverantwortliche aktivieren');
$I->waitForElement('#new-foodsaver-search', 5);

addToTeam($I, $bibC);
addToTeam($I, $foodsaverD);
addToTeam($I, $foodsaverE);
addToTeam($I, $foodsaverF);

$I->waitForActiveAPICalls();
$I->seeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $bibC['id'],
	'active' => MembershipStatus::MEMBER,
	'verantwortlich' => 0,
]);
$I->seeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $foodsaverF['id'],
	'active' => MembershipStatus::MEMBER,
	'verantwortlich' => 0,
]);
$I->waitForElement('button.reload-page', 5);
$I->click('button.reload-page');

/* Promote another store manager */
$I->click('Ansicht für Betriebsverantwortliche aktivieren');
$I->click($bibC['name'] . ' ' . $bibC['nachname'], '.store-team');
$I->click('Verantwortlich machen', '.member-actions');
$I->waitForActiveAPICalls();
$I->seeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $bibC['id'],
	'active' => MembershipStatus::MEMBER,
	'verantwortlich' => 1,
]);

/* Edit the store to see that team does not change */
$I->amOnPage($I->storeEditUrl($storeId));
$I->click('Senden');
$I->see('Änderungen wurden gespeichert');

/* Reload to get rid of green overlay */
$I->amOnPage($I->storeUrl($storeId));

$I->waitForElement('.store-team tr.table-warning[data-pk="' . $bibA['id'] . '"]', 5);
$I->waitForElement('.store-team tr.table-warning[data-pk="' . $bibC['id'] . '"]', 5);
$I->see($bibA['handy'], '.store-team');
$I->see($bibC['name'] . ' ' . $bibC['nachname'], '.store-team');
$I->see($foodsaverD['name'] . ' ' . $foodsaverD['nachname'], '.store-team');
$I->see($foodsaverE['name'] . ' ' . $foodsaverE['nachname'], '.store-team');
$I->see($foodsaverF['name'] . ' ' . $foodsaverF['nachname'], '.store-team');

/* Demote store manager to regular team member */
$I->click('Ansicht für Betriebsverantwortliche aktivieren');
$I->click($bibC['name'] . ' ' . $bibC['nachname'], '.store-team');
$I->click('Als Betriebsverantwortliche*n entfernen', '.member-actions');
$I->seeInPopup('die Verantwortung für diesen Betrieb entziehen?');
$I->cancelPopup();
$I->seeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $bibC['id'],
	'verantwortlich' => 1,
]);
$I->waitForElement('.store-team tr.table-warning[data-pk="' . $bibC['id'] . '"]', 2);
$I->click('Als Betriebsverantwortliche*n entfernen', '.member-actions');
$I->seeInPopup('die Verantwortung für diesen Betrieb entziehen?');
$I->acceptPopup();
$I->waitForActiveAPICalls();
$I->dontSeeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $bibC['id'],
	'verantwortlich' => 1,
]);

$I->waitForElement('.store-team tr.table-warning[data-pk="' . $bibA['id'] . '"]', 5);
/* Make sure the demoted member is no longer displayed as store manager */
$I->waitForElement('.store-team tr[data-pk="' . $bibC['id'] . '"]:not(.table-warning)', 5);
$I->see($foodsaverD['name'] . ' ' . $foodsaverD['nachname'], '.store-team');
$I->see($foodsaverE['name'] . ' ' . $foodsaverE['nachname'], '.store-team');
$I->see($foodsaverF['name'] . ' ' . $foodsaverF['nachname'], '.store-team');

// convert a member to jumper (standby list)
$I->click($foodsaverD['name'] . ' ' . $foodsaverD['nachname'], '.store-team');
$I->click('Auf die Springerliste', '.member-actions');
$I->waitForActiveAPICalls();
// check that the jumper is still displayed as team member (but with muted colors)
$I->see($foodsaverF['name'] . ' ' . $foodsaverF['nachname'], '.store-team');
$I->waitForElement('.store-team #member-' . $foodsaverD['id'] . '.member-info.jumper', 5);

$jumperIds = $I->grabColumnFromDatabase('fs_betrieb_team', 'foodsaver_id', [
	'betrieb_id' => $storeId,
	'active' => MembershipStatus::JUMPER,
]);
$jumpers = array_column([$foodsaverD], 'id');
$I->assertEquals($jumperIds, $jumpers);

// remove a member from the team entirely
$I->click($foodsaverF['name'] . ' ' . $foodsaverF['nachname'], '.store-team');
$I->click('Aus dem Team entfernen', '.member-actions');
$I->seeInPopup('aus diesem Betriebs-Team entfernen?');
$I->cancelPopup();
// confirm alert this time
$I->click('Aus dem Team entfernen', '.member-actions');
$I->seeInPopup('aus diesem Betriebs-Team entfernen?');
$I->acceptPopup();
$I->waitForActiveAPICalls();
$I->dontSee($foodsaverF['name'] . ' ' . $foodsaverF['nachname'], '.store-team');
$I->dontSeeInDatabase('fs_betrieb_team', [
	'betrieb_id' => $storeId,
	'foodsaver_id' => $foodsaverF['id'],
]);

$storeTeam = array_column([$bibA, $bibC, $foodsaverE], 'id');
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
