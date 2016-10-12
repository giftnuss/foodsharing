<?php
$I = new AcceptanceTester($scenario);

$I->wantTo('Ensure I can create a food basket');

$I->login('usera@example.com', 'usera');

$I->amOnPage('/');