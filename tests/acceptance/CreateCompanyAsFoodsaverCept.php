<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('try to create a new company as foodsaver');

$pass = sq('pass');

$foodsaver = $I->createFoodsaver($pass);

$I->login($foodsaver['email'], $pass);

// check if user see Link to "Neuen Betrieb eintragen"
$I->amOnPage('/?page=betrieb&bid=903');
$I->dontSee('Neuen Betrieb eintragen');

// check if foodsaver without bieb-quiz can add new stores

$I->amOnPage('/?page=betrieb&a=new&bid=903');
$I->seeCurrentUrlEquals('/?page=settings&sub=upgrade/up_bip'); // it redirects
