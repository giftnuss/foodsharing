<?php

$I = new CliTester($scenario);
$I->am('Cron');
$I->wantTo('see that lookupEmail method works');
$fsA = $I->createFoodsaver(null, array('email' => 'tollerBenutzer@ichbinneemail.de', 'last_login' => '2016-11-11 11:11:00'));
$fsB = $I->createFoodsaver(null, array('email' => 'zweiterBenutzer@gmail.com', 'last_login' => null));
$fsC = $I->createFoodsaver(null, array('email' => '2zweiterBenutzer@gmail.com', 'last_login' => (new DateTime())->format('Y-m-d H:i:s')));
$I->amInPath('');
$I->runShellCommand('php -f run.php Lookup lookup tests/_data/emaillist.csv');
$I->seeInShellOutput($fsA['id'] . ',');
$I->seeInShellOutput($fsB['id'] . ',');
$I->runShellCommand('php -f run.php Lookup deleteOldUsers tests/_data/emaillist.csv');

$a = $I->grabFromDatabase('fs_foodsaver', 'deleted_at', array('id' => $fsA['id']));
$I->assertNotNull($a);

$b = $I->grabFromDatabase('fs_foodsaver', 'deleted_at', array('id' => $fsB['id']));
$I->assertNull($b);

$c = $I->grabFromDatabase('fs_foodsaver', 'deleted_at', array('id' => $fsC['id']));
$I->assertNull($c);
