<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('Join another region');

$user = $I->createFoodsaver();
$region = $I->createRegion(null, 0, 3);
$ambassador = $I->createAmbassador(null, ['bezirk_id' => $region['id']]);
$I->addBezirkAdmin($region['id'], $ambassador['id']);

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

$I->seeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $ambassador['id']]);
$I->seeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $user['id'], 'bezirk_id' => $region['id']]);
