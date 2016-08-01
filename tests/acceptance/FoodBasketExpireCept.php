<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Ensure that the expired Foodbasket show another text');
$I->amOnPage('/essenskoerbe/1');
$I->see('Dieser Essenskorb wurde bereits abgeholt');

$I->wantTo('Ensure that the not expired Foodbasket show the Basket');
$I->amOnPage('/essenskoerbe/2');
$I->see('###TEST###');