<?php

$I = new AcceptanceTester($scenario);
$I->wantTo('add a foodsaver to a working group by seeing their ID in the tag select');

$group = $I->createWorkingGroup('a test group');
$foodsaver = $I->createFoodsaver(null, array('name' => 'WorkingGroupTestUser', 'nachname' => 'lastNameOfThat'));
$admin = $I->createFoodsaver();
$I->addBezirkMember($group['id'], $admin['id']);
$I->addBezirkAdmin($group['id'], $admin['id']);

$I->login($admin['email']);
$I->amOnPage($I->groupEditUrl($group['id']));
$I->waitForElement('.tagedit-listelement-old');
$I->addInTagSelect('lastNameOfThat (' . $foodsaver['id'] . ')', '#work_group_form_members');
$I->click('Änderungen speichern');
$I->seeInDatabase('fs_foodsaver_has_bezirk', array('bezirk_id' => $group['id'], 'foodsaver_id' => $foodsaver['id']));
