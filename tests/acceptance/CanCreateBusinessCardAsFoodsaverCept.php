<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('create a businesscard being a foodsaver');

$pass = sq('pass');

$foodsaver = $I->createFoodsaver($pass, array('handy' => '+4915100000'));

$I->login($foodsaver['email'], $pass);

$I->amOnPage('/?page=bcard');

$I->waitForText('Deine foodsharing Visitenkarte');
