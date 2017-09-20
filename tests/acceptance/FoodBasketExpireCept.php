<?php

$I = new AcceptanceTester($scenario);

$pass = sq('pass');
$foodsaver = $I->createFoodsaver($pass);

$expired_id = $I->haveInDatabase('fs_basket', [
	'foodsaver_id' => $foodsaver['id'],
	'status' => 1,
	'time' => '2016-04-04 11:47:52',
	'until' => '2016-05-16',
	'description' => 'ICH BIN ABGELAUFEN'
]);

$not_expired_id = $I->haveInDatabase('fs_basket', [
	'foodsaver_id' => $foodsaver['id'],
	'status' => 1,
	'time' => '2016-08-01 11:47:43',
	'until' => '2030-08-15',
	'description' => '###TEST###'
]);

$I->wantTo('Ensure that the expired Foodbasket show another text');
$I->amOnPage('/essenskoerbe/' . $expired_id);
$I->see('Dieser Essenskorb wurde bereits abgeholt');

$I->wantTo('Ensure that the not expired Foodbasket show the Basket');
$I->amOnPage('/essenskoerbe/' . $not_expired_id);
$I->see('###TEST###');
