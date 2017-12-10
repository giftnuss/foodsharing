<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('ensure the homepage works');
$I->amOnPage('/');
$I->see('let good food go bad');
