<?php

$board_id = \Foodsharing\Modules\Core\DBConstants\Region\RegionIDs::TEAM_BOARD_MEMBER;
$administration_id = \Foodsharing\Modules\Core\DBConstants\Region\RegionIDs::TEAM_ADMINISTRATION_MEMBER;
$alumni_id = \Foodsharing\Modules\Core\DBConstants\Region\RegionIDs::TEAM_ALUMNI_MEMBER;
$I = new AcceptanceTester($scenario);

$boardMember = $I->createFoodsaver();
$I->addBezirkMember($board_id, $boardMember['id']);
$administrationMember = $I->createFoodsaver();
$I->addBezirkMember($administration_id, $administrationMember['id']);
$alumniMember = $I->createFoodsaver();
$I->addBezirkMember($alumni_id, $alumniMember['id']);

$I->wantTo('Check if the team page lists board and active members and the alumni subpage only alumni members');

$I->amOnPage('/team');
$I->see('Kontaktanfragen');
$I->see($boardMember['name']);
$I->see($administrationMember['name']);
$I->dontSee($alumniMember['name']);

$I->amOnPage('/team/ehemalige');
$I->dontSee($boardMember['name']);
$I->dontSee($administrationMember['name']);

$I->see($alumniMember['name']);
