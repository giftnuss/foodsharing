<?php

$I = new CliTester($scenario);
$I->am('Admin');
$I->wantTo('migrate the forum posts');
$I->amInPath('');
$region = $I->createRegion();
$user = $I->createFoodsaver();
$thread = $I->addForumTheme($region['id'], $user['id']);
$post = $I->addForumThemePost($thread['id'], $user['id'], ['body' => 'Hallo<br />
Ein Testpost<br />
mit Zeilenumbrüchen']);
$I->runShellCommand('php -f run.php Migrate ForumPostRemoveBr');
$I->seeInShellOutput('Migrated');
$I->seeInDatabase('fs_theme_post', ['id' => $post['id'], 'body' => 'Hallo
Ein Testpost
mit Zeilenumbrüchen']);
