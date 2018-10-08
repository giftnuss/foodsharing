<?php

$I = new ApiTester($scenario);
$I->wantTo('get a 404 response when I want to access not existant page');

$request = array('page' => 'thishopefullydoesnotexist');
$I->sendGET('/', $request);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);

$request = array('page' => 'search');
$I->sendGET('/', $request);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
