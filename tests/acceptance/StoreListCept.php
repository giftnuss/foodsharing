<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('click through vue generated store list pages');

$region = $I->createRegion('A region I test with');

$extra_params = ['bezirk_id' => $region['id']];
$bibA = $I->createStoreCoordinator(null, $extra_params);

for ($i = 0; $i < 30; ++$i) {
	$I->createStore($region['id']);
}

$I->login($bibA['email']);
$I->amOnPage($I->storeListUrl($region['id']));

// Page 1 active and Page 2 available
$I->see('1', '.page-item.active .page-link');
$I->see('2', '.page-item .page-link');

// go to page 2
$I->click('.page-link[aria-posinset="2"]');

// Page 2 active and Page 1 available
$I->see('1', '.page-item .page-link');
$I->see('2', '.page-item.active .page-link');
