<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('ensure various pages work');

$I->amOnPage('/');
/* digital climate strike overlay overlaps leaflet attribution, but it is fine after closing */
$I->setCookie('_DIGITAL_CLIMATE_STRIKE_WIDGET_CLOSED_', 'true');
$I->see('Lebensmittel');

$I->amOnPage('/essenskoerbe');
$I->seeCurrentUrlEquals('/essenskoerbe/find'); // it redirects
$I->see('Essenskörbe');

$I->amOnPage('/faq');
$I->see('1. Ist es kostenlos, sich bei foodsharing.de anzumelden?');

$I->amOnPage('/ratgeber');
$I->see('Da Lebensmittel uns am Leben erhalten, sollte man mit ihnen auch respektvoll umgehen');

$I->amOnPage('/karte');
$I->see('Essenskörbe');
$I->see('Fair-Teiler');
$I->seeElement('#map');
// this might need a wait beforehand so leaflet had time to load asynchronously.
// Figure out & add this wait as soon as this fails once!
$I->seeElement('.leaflet-map-pane'); // leaflet loaded correctly
$I->elementVisibleInViewport('.leaflet-control-attribution');
