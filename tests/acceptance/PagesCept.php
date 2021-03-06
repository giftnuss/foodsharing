<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('ensure various pages work');

$I->amOnPage('/');
$I->see('Lebensmittel');

$I->amOnPage('/essenskoerbe');
$I->seeCurrentUrlEquals('/essenskoerbe/find'); // it redirects
$I->see('Essenskörbe');

$I->amOnPage('/karte');
$I->see('Essenskörbe');
$I->see('Fairteiler');
$I->seeElement('#map');
// this might need a wait beforehand so leaflet had time to load asynchronously.
// Figure out & add this wait as soon as this fails once!
$I->seeElement('.leaflet-map-pane'); // leaflet loaded correctly
$I->seeElement('.leaflet-control-attribution');
