<?php

$I = new CliTester($scenario);
$I->am('Cron');
$I->wantTo('see that mailbox update method exists and starts, without caring for any errors');
$I->amInPath('');
$I->runShellCommand('php -f run.php Mails mailboxupdate', false);
$I->seeInShellOutput('Starting mails::mailboxupdate...');
