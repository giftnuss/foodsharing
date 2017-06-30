<?php
$board_id = 1373;
$active_id = 1565;
$alumni_id = 1564;
$I = new AcceptanceTester($scenario);

$boardmember = $I->createFoodsaver();
$I->addBezirkMember($board_id, $boardmember['id']);
$activemember = $I->createFoodsaver();
$I->addBezirkMember($active_id, $activemember['id']);
$alumnimember = $I->createFoodsaver();
$I->addBezirkMember($alumni_id, $alumnimember['id']);

$I->wantTo('Check if the team page lists board and active members and the alumni subpage only alumni members');

$I->amOnPage('/team');
$I->see('Kontaktanfragen');
$I->see($boardmember['name']);
$I->see($activemember['name']);
$I->dontSee($alumnimember['name']);

$I->amOnPage('/team/ehemalige');
$I->dontSee($boardmember['name']);
$I->dontSee($activemember['name']);

$I->see($alumnimember['name']);
