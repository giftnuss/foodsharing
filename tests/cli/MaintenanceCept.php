<?php

$I = new CliTester($scenario);
$I->am('Cron');
$I->wantTo('see that maintenance jobs do execute');
$I->amInPath('');
$I->runShellCommand('php -f run.php maintenance daily');
$I->seeInShellOutput('Starting maintenance::daily...');
