<?php

$I = new CliTester($scenario);

$store = $I->createStore(1);
$coordinator = $I->createStoreCoordinator();
$fetcher = $I->createFoodsaver();
$I->addStoreTeam($store['id'], $coordinator['id'], true, false, true);
$I->addStoreTeam($store['id'], $fetcher['id'], false, false, true);

$dataset = array('betrieb_id' => $store['id'], 'dow' => 0, 'time' => '15:00:00', 'fetcher' => 1);
$I->haveInDatabase('fs_abholzeiten', $dataset);
$dataset = array('betrieb_id' => $store['id'], 'dow' => 1, 'time' => '20:00:00', 'fetcher' => 1);
$I->haveInDatabase('fs_abholzeiten', $dataset);
$dataset = array('betrieb_id' => $store['id'], 'dow' => 2, 'time' => '20:00:00', 'fetcher' => 1);
$I->haveInDatabase('fs_abholzeiten', $dataset);
$dataset = array('betrieb_id' => $store['id'], 'dow' => 3, 'time' => '20:00:00', 'fetcher' => 1);
$I->haveInDatabase('fs_abholzeiten', $dataset);
$dataset = array('betrieb_id' => $store['id'], 'dow' => 4, 'time' => '20:00:00', 'fetcher' => 1);
$I->haveInDatabase('fs_abholzeiten', $dataset);
$dataset = array('betrieb_id' => $store['id'], 'dow' => 5, 'time' => '20:00:00', 'fetcher' => 1);
$I->haveInDatabase('fs_abholzeiten', $dataset);
$dataset = array('betrieb_id' => $store['id'], 'dow' => 6, 'time' => '19:00:00', 'fetcher' => 1);
$I->haveInDatabase('fs_abholzeiten', $dataset);

/* fetchers where signed up for sunday, monday, wednesday. It was tuesday morning 01:45am when an email should have been sent, instead it came on wednesday 01:45 */
