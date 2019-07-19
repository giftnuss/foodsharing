<?php

$I = new CliTester($scenario);

$store = $I->createStore(1);
$fetcher_unconfirmed_past_1 = $I->createFoodsaver();
$fetcher_unconfirmed_past_2 = $I->createFoodsaver();
$fetcher_unconfirmed_future = $I->createFoodsaver();

$fetcher_confirmed_past = $I->createFoodsaver();
$fetcher_confirmed_future = $I->createFoodsaver();

$I->addStoreTeam($store['id'], $fetcher_unconfirmed_past_1['id'], false, false, true);
$I->addStoreTeam($store['id'], $fetcher_unconfirmed_past_2['id'], false, false, true);
$I->addStoreTeam($store['id'], $fetcher_unconfirmed_future['id'], false, false, true);

$I->addStoreTeam($store['id'], $fetcher_confirmed_past['id'], false, false, true);
$I->addStoreTeam($store['id'], $fetcher_confirmed_future['id'], false, false, true);

$dataset_unconfirmed_past_1 = array('foodsaver_id' => $fetcher_unconfirmed_past_1['id'], 'betrieb_id' => $store['id'], 'date' => '2001-02-25 08:55', 'confirmed' => 0);
$I->haveInDatabase('fs_abholer', $dataset_unconfirmed_past_1);

$dataset_unconfirmed_past_2 = array('foodsaver_id' => $fetcher_unconfirmed_past_2['id'], 'betrieb_id' => $store['id'], 'date' => '2008-08-25 17:55', 'confirmed' => 0);
$I->haveInDatabase('fs_abholer', $dataset_unconfirmed_past_2);

$dataset_unconfirmed_future = array('foodsaver_id' => $fetcher_unconfirmed_future['id'], 'betrieb_id' => $store['id'], 'date' => '2500-06-25 22:20', 'confirmed' => 0);
$I->haveInDatabase('fs_abholer', $dataset_unconfirmed_future);

$dataset_confirmed_past = array('foodsaver_id' => $fetcher_confirmed_past['id'], 'betrieb_id' => $store['id'], 'date' => '2008-11-25 17:55', 'confirmed' => 1);
$I->haveInDatabase('fs_abholer', $dataset_confirmed_past);

$dataset_confirmed_future = array('foodsaver_id' => $fetcher_confirmed_future['id'], 'betrieb_id' => $store['id'], 'date' => '2500-05-25 22:20', 'confirmed' => 1);
$I->haveInDatabase('fs_abholer', $dataset_confirmed_future);

$I->am('Cron');
$I->wantTo('see that maintenance jobs do execute');
$I->amInPath('');
$I->runShellCommand('php -f run.php Maintenance daily');

$I->seeInShellOutput('delete unconfirmed fetchdates');
$I->seeInShellOutput('2 deleted');
$I->seeInShellOutput('updating Wien BIEB group');
