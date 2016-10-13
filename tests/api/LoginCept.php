<?php
$I = new ApiTester($scenario);

$I->wantTo('Login as an existing user');

$pass = sq('pass');
$foodsaver = $I->createFoodsaver($pass);

$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$I->sendPOST('/?page=login', [
	'email_adress' => $foodsaver['email'],
	'password' => $pass
]);

$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
$I->seeHtml();