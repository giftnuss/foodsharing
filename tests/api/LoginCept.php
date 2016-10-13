<?php
$I = new ApiTester($scenario);

$I->wantTo('login');

$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');

$I->sendPOST('/?page=login', [
	'email_adress' => 'usera@example.com',
	'password' => 'usera'
]);

$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
$I->seeHtml();