<?php

$I = new CliTester($scenario);
$I->am('Cron');
$I->wantTo('see that maintenance jobs do execute');
$I->amInPath('');
$I->runShellCommand('php -f run.php Maintenance daily');
$I->seeInShellOutput('Starting Maintenance::daily...');
