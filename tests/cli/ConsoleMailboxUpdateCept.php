<?php

$I = new CliTester($scenario);
$I->am('Cron');
$I->wantTo('see that CronCommand exists, starts, and exits with empty output');
$I->amInPath('');
$I->runShellCommand('php -f bin/console foodsharing:cron', false);
$I->seeResultCodeIs(0);
$I->seeShellOutputMatches('/^$/');
