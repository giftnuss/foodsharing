<?php

$I = new ApiTester($scenario);
$I->wantTo('get a 404 response when I want to access not existant page');

$request = ['page' => 'thishopefullydoesnotexist'];
$I->sendGET('/', $request);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);

$request = ['page' => 'search'];
$I->sendGET('/', $request);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
