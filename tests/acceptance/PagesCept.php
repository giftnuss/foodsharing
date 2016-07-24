<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('ensure various pages work');

$I->amOnPage('/');
$I->see('Die Berliner');

$I->amOnPage('/essenskoerbe');
$I->seeCurrentUrlEquals('/essenskoerbe/find'); // it redirects
$I->see('Essenkörbe');

$I->amOnPage('/faq');
$I->see('1. Ist es kostenlos, sich bei foodsharing.de anzumelden?');

$I->amOnPage('/ratgeber');
$I->see('Da Lebensmittel uns am Leben erhalten, sollte man mit ihnen auch respektvoll umgehen');

$I->amOnPage('/karte');
$I->see('Essenskörbe');
$I->see('Fair-Teiler');
$I->seeElement('#map');
$I->seeElement('.leaflet-map-pane'); // leaflet loaded correctly