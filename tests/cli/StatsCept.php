<?php
$I = new CliTester($scenario);
$I->am('Cron');
$I->wantTo('see that stat generation jobs do execute');
$I->amInPath('');
$I->runShellCommand('php -f run.php stats bezirke');
$I->seeInShellOutput('Starting stats::bezirke...');
$I->runShellCommand('php -f run.php stats betriebe');
$I->seeInShellOutput('Starting stats::betriebe...');
$I->runShellCommand('php -f run.php stats foodsaver');
$I->seeInShellOutput('Starting stats::foodsaver...');
