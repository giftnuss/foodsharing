<?php
use Foodsharing\Modules\Core\DBConstants\Region\Type;

$I = new AcceptanceTester($scenario);

$I->wantTo('Check if someone editing a profile sees return to profile button and if return to profile button points to the edited profile');

$region = $I->createRegion(null, 0, Type::REGION);

$member = $I->createFoodsaver();
$ambassador = $I->createAmbassador(null, ['bezirk_id' => $region['id']]);

$I->addBezirkAdmin($region['id'], $ambassador['id']);
$I->addBezirkMember($region['id'], $member['id']);

$I->login($ambassador['email']);

$I->amOnPage('/?page=foodsaver&a=edit&id=' . $member['id']);

$I->see('Zurück zum Profil');
$I->see($member['name']);
$I->click('Zurück zum Profil');

$I->seeCurrentUrlEquals('/profile/' . $member['id']);
