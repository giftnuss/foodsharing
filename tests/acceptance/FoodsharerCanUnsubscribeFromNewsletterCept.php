<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('unsubscribe from the newsletter via settings menu');

$foodsaver = $I->createFoodsaver(null, ['name' => 'fs', 'nachname' => 'one', 'newsletter' => '1']);

$I->login($foodsaver['email']);

$I->amOnPage('/?page=settings&sub=info');
$I->waitForPageBody();

$I->seeOptionIsSelected('#newsletter-wrapper', 'Ja');
$I->selectOption('#newsletter-wrapper', 'Nein');

$I->click('Speichern');

$I->waitForPageBody();
$I->seeInDatabase('fs_foodsaver', [
	'id' => $foodsaver['id'],
	'newsletter' => 0
]);
$I->seeOptionIsSelected('#newsletter-wrapper', 'Nein');
