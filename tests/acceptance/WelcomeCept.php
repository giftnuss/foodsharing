<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('ensure the homepage works');
$I->amOnPage('/');
$I->see('Sei dabei beim foodsharing festival 2018');
