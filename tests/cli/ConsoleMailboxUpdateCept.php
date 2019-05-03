<?php

$I = new CliTester($scenario);
$I->am('Cron');
$I->wantTo('see that CronCommand exists and starts, without caring for any errors');
$I->amInPath('');
$I->runShellCommand('php -f bin/console foodsharing:cron', false);
$I->seeInShellOutput('mailscontrl');
