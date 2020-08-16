<?php

use Foodsharing\Modules\Core\DBConstants\Region\WorkgroupFunction;

$I = new AcceptanceTester($scenario);

$I->wantTo('Join another region');

/* This user is explicitly unverified to trigger #404; also actually newly registered users are unverified. */
$user = $I->createFoodsaver(null, ['verified' => 0]);
$region = $I->createRegion(null, ['parent_id' => 0]);
$ambassador = $I->createAmbassador(null, ['bezirk_id' => $region['id']]);
$I->addRegionAdmin($region['id'], $ambassador['id']);
$welcomeAdmin = $I->createFoodsaver(null, ['verified' => 0]);
$welcomeGroup = $I->createWorkingGroup('Begrüßung', ['parent_id' => $region['id']]);
$I->haveInDatabase('fs_region_function', ['region_id' => $welcomeGroup['id'], 'function_id' => WorkgroupFunction::WELCOME, 'target_id' => $region['id']]);
$I->addRegionAdmin($welcomeGroup['id'], $welcomeAdmin['id']);

$I->login($user['email']);
/*
 * As the user does not have a home region, it gets to select one by default. Maybe this behaviour changes and we need
 * to open the choser?
 */
/*$I->moveMouseOver('//*[contains(@id, "mainMenu")]/li[contains(. ,"Bezirke")]');
$I->click('Bezirk beitreten');
*/

$I->see('Bitte auswählen');
$I->selectOption('#xv-childbezirk-0', $region['name']);
$I->moveMouseOver('#becomebezirkchooser-button');
$I->click('#becomebezirkchooser-button');
$I->waitForElementVisible('//a[contains(text(), "Neues Thema")]');

$I->dontSeeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $ambassador['id']]);
$I->seeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $welcomeAdmin['id']]);
$I->seeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $user['id'], 'bezirk_id' => $region['id']]);
