<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('ensure various pages work');

$I->amOnPage('/');
$I->see('Lebensmittel');

$I->amOnPage('/essenskoerbe');
$I->seeCurrentUrlEquals('/essenskoerbe/find'); // it redirects
$I->see('Essenskörbe');

$fs = $I->createFoodsaver();
$I->haveInDatabase('fs_faq', ['foodsaver_id' => $fs['id'], 'faq_kategorie_id' => 1, 'name' => 'Is this a test FAQ entry?', 'answer' => 'Yes, it is!']);
$I->amOnPage('/faq');
$I->see('Is this a test FAQ entry');

$I->amOnPage('/ratgeber');
$I->see('Da Lebensmittel uns am Leben erhalten, sollte man mit ihnen auch respektvoll umgehen');

$I->amOnPage('/karte');
$I->see('Essenskörbe');
$I->see('Fair-Teiler');
$I->seeElement('#map');
// this might need a wait beforehand so leaflet had time to load asynchronously.
// Figure out & add this wait as soon as this fails once!
$I->seeElement('.leaflet-map-pane'); // leaflet loaded correctly
$I->seeElement('.leaflet-control-attribution');
