<?php

$I = new AcceptanceTester($scenario);

$I->wantTo('see that timezones match all to Europe/Berlin');

$foodsaver = $I->createFoodsaver();
$description = 'test foodbasket with bananas';

$I->login($foodsaver['email'], 'password');
$I->amOnPage('/');

$I->click('#infobar .basket a');
$I->see('Neuen Essenskorb anlegen');

$I->click('Neuen Essenskorb anlegen');
$I->waitForText('Essenskorb anbieten');

$I->fillField('description', $description);

$min_time = new DateTime('now', new DateTimeZone('Europe/Berlin'));
$max_time = new DateTime('now', new DateTimeZone('Europe/Berlin'));
$max_time->add(new DateInterval('PT15S'));

$I->click('Essenskorb veröffentlichen');

$I->waitForElementVisible('#pulse-info', 4);
$I->see('Danke Dir! Der Essenskorb wurde veröffentlicht!');

$id = $I->grabFromDatabase('fs_basket', 'id', ['foodsaver_id' => $foodsaver['id'], 'description' => $description]);
$time = $I->grabFromDatabase('fs_basket', 'time', ['id' => $id]);

$I->formattedDateInRange($min_time, $max_time, 'Y-m-d H:i:s', $time);

$time_hm = substr(explode(' ', $time)[1], 0, 5);

$I->amOnPage('/essenskoerbe/' . $id);
$I->see($time_hm . ' Uhr');
