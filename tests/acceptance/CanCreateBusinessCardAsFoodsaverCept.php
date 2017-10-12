<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('create a businesscard being a foodsaver');

$pass = sq('pass');

$foodsaver = $I->createFoodsaver($pass);

$I->login($foodsaver['email'], $pass);

$I->amOnPage('/?page=bcard');

$I->see('Deine foodsharing Visitenkarte');
