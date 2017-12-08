<?php

$I = new CliTester($scenario);
$I->am('Cron');
$I->wantTo('see that lookupEmail method works');
$fsA = $I->createFoodsaver(null, array('email' => 'tollerBenutzer@ichbinneemail.de', 'last_login' => '2017-11-11 11:11:00'));
$fsB = $I->createFoodsaver(null, array('email' => 'zweiterBenutzer@gmail.com'));
$fsC = $I->createFoodsaver(null, array('email' => '2zweiterBenutzer@gmail.com'));
$I->amInPath('');
$I->runShellCommand('php -f run.php lookup lookupFile tests/_data/emaillist.csv');
$I->seeInShellOutput($fsA['id'] . ',');
$I->seeInShellOutput($fsB['id'] . ',');
