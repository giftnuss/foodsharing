<?php

$I = new ApiTester($scenario);
$I->wantTo('see the release notes being rendered into html');

$request = ['page' => 'content', 'sub' => 'releaseNotes'];
$I->sendGET('/', $request);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
$I->seeResponseContains('Was ist neu?');
