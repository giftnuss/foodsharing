<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('ensure you can login');
$I->amOnPage('/');
$I->fillField('email_adress', 'usera@example.com');
$I->fillField('password', 'usera');
$I->click('#loginbar input[type=submit]');
$I->see('Willkommen User!');