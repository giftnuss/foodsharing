<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('ensure the homepage works');
$I->amOnPage('/');
$I->see('foodsharing ist und bleibt kostenlos');
