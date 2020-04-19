<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('join a home district via dashboard banner if none is choosen yet.');

/*
This foodsaver has bezirk_id 0, so no home district
*/
$foodsaver = $I->createFoodsaver();

$I->login($foodsaver['email']);
$I->amOnPage('/?page=dashboard');
$I->executeJS("$('button:contains(Schließen)').trigger('click')");
$I->click('Bitte wähle einen Stammbezirk aus.');
$I->see('Wähle den Bezirk aus, in dem Du aktiv werden möchtest!');
