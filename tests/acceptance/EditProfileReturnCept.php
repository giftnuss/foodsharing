<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('Check if an someone editing a profile sees return to profile button and if return to profile button points to the edited profile');

$member = $I->createFoodsaver();
$region = $I->createRegion(null, 0, 3);
$I->addBezirkMember($region['id'], $member['id']);
$ambassador = $I->createAmbassador(null, ['bezirk_id' => $region['id']]);
$I->addBezirkAdmin($region['id'], $ambassador['id']);

$I->login($ambassador['email']);

$I->amOnPage('/?page=foodsaver&a=edit&id=' . $member['id']);

$I->see('Zurück zum Profil');
$I->see($member['name']);
$I->click('Zurück zum Profil');

$I->seeCurrentUrlEquals('/profile/' . $member['id']);
