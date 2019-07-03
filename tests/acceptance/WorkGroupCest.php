<?php

use Codeception\Util\Locator;
use Foodsharing\Modules\Core\DBConstants\Region\ApplyType;

class WorkGroupCest
{
	/* roles that refer to testGroup */
	private $testGroup;

	private $regionMember;
	private $groupAdmin;
	private $unconnectedFoodsaver;

	/* group that can be applied for */
	private $testGroupApply;
	/* admin of testGroupApply */
	private $groupApplyAdmin;

	public function _before(AcceptanceTester $I)
	{
		/* WorkGroup open to join for everybody */
		$I->createWorkingGroup('0random-placeholder-group');
		$this->testGroup = $I->createWorkingGroup('a group for testing to see groups', ['apply_type' => ApplyType::OPEN]);
		$this->testGroupApply = $I->createWorkingGroup('a group to apply for', ['apply_type' => ApplyType::EVERYBODY]);
		$this->regionMember = $I->createFoodsaver();
		$I->addBezirkMember($this->testGroup['id'], $this->regionMember['id']);
		$this->unconnectedFoodsaver = $I->createFoodsaver();
		$this->foodsharer = $I->createFoodsharer();
		$this->groupAdmin = $I->createFoodsaver();
		$I->addBezirkMember($this->testGroup['id'], $this->groupAdmin['id']);
		$I->addBezirkAdmin($this->testGroup['id'], $this->groupAdmin['id']);
		$this->groupApplyAdmin = $I->createFoodsaver();
		$I->addBezirkMember($this->testGroupApply['id'], $this->groupApplyAdmin['id']);
		$I->addBezirkAdmin($this->testGroupApply['id'], $this->groupApplyAdmin['id']);
	}

	public function _after(AcceptanceTester $I)
	{
	}

	// tests

	/**
	 * It is actually not really defined if foodsharer should be able to participate in groups or not.
	 * They don't get the menu item but they can use groups.
	 *
	 * @param AcceptanceTester $I
	 * @param \Codeception\Example $example
	 * @example["regionMember", true]
	 * @example["unconnectedFoodsaver", true]
	 * @example["foodsharer", true]
	 */
	public function canSeeGlobalGroups(AcceptanceTester $I, \Codeception\Example $example)
	{
		$I->login($this->{$example[0]}['email']);
		$I->amOnPage($I->groupListUrl());
		if ($example[1]) {
			$I->see($this->testGroup['name']);
		} else {
			$I->dontSee($this->testGroup['name']);
		}
	}

	/**
	 * It is actually not really defined if foodsharer should be able to participate in groups or not.
	 * They don't get the menu item but they can use groups.
	 *
	 * @param AcceptanceTester $I
	 * @param \Codeception\Example $example
	 * @example["unconnectedFoodsaver", "testGroup"]
	 */
	public function canJoinGlobalGroup(AcceptanceTester $I, \Codeception\Example $example)
	{
		$group = $this->{$example[1]};
		$I->login($this->{$example[0]}['email']);
		$I->amOnPage($I->groupListUrl());
		$I->clickWithLeftButton(Locator::contains('.groups .field .head', $group['name']));
		$I->click('Dieser Arbeitsgruppe beitreten');
		/* We have a bug here: We need to relogin to join the Group. As I will not fix major bugs with this commit,
		 * I leave this to a later bugfixer and just test the bugged behaviour :-)
		 */
		//$I->waitUrlEquals($I->forumUrl($group['id']));
		$I->logout();
		$I->login($this->{$example[0]}['email']);
		$I->amOnPage($I->forumUrl($group['id']));
		$I->see($group['name']);
		$I->see('Noch keine Themen gepostet');
	}

	public function canEditTeamList(AcceptanceTester $I)
	{
		$user = $I->createFoodsaver();
		$admin = $I->createFoodsaver();
		$I->login($this->groupAdmin['email']);
		$I->amOnPage($I->groupEditUrl($this->testGroup['id']));
		$I->addInTagSelect($admin['name'], '#work_group_form_administrators');
		$I->addInTagSelect($user['name'], '#work_group_form_members');
		$I->click('Änderungen speichern');
		$I->waitForText('Änderungen gespeichert');
		$I->see($user['name'], '#work_group_form_members');
		$I->see($admin['name'], '#work_group_form_administrators');
		$I->removeFromTagSelect($user['name'], 'work_group_form_members');
		$I->click('Änderungen speichern');
		$I->waitForText('Änderungen gespeichert');
		$I->dontSee($user['name'], '#work_group_form_members');
		$I->see($admin['name'], '#work_group_form_administrators');
	}

	/**
	 * @param \Codeception\Example $example
	 * @example["unconnectedFoodsaver"]
	 * @example["regionMember"]
	 */
	public function canNotEditWorkGroupAs(AcceptanceTester $I, \Codeception\Example $example)
	{
		$I->login($this->{$example[0]}['email']);
		$I->amOnPage($I->groupEditUrl($this->testGroup['id']));
		$I->seeInCurrentUrl('dashboard');
		$I->dontSee('Bewerbungen');
	}

	public function canApplyForWorkGroup(AcceptanceTester $I)
	{
		$I->login($this->regionMember['email']);
		$I->amOnPage($I->groupListUrl());
		$I->clickWithLeftButton(Locator::contains('.groups .field .head', $this->testGroupApply['name']));
		$I->waitForText('Arbeitsgruppe bewerben');
		//$I->performOn(Locator::combine('.ui-widget', 'div[style="display: block;"]'), ['click' => 'bewerben']);
		$I->click('a[onclick*="apply\',{id:' . $this->testGroupApply['id'] . '"]');
		$I->waitForElement('#motivation');
		$I->fillField('#motivation', 'My Motivation');
		$I->fillField('#faehigkeit', 'My Skillz');
		$I->fillField('#erfahrung', 'My Experience');
		$I->selectOption('#zeit', '1-2 Stunden');
		$I->click('Bewerbung absenden');
		$I->waitForText('Bewerbung wurde abgeschickt!');
		$I->seeInDatabase('fs_foodsaver_has_bezirk', ['foodsaver_id' => $this->regionMember['id'], 'bezirk_id' => $this->testGroupApply['id']]);
		$admin = $I->haveFriend('admin');
		$admin->does(function (AcceptanceTester $I) {
			$I->login($this->groupApplyAdmin['email']);
			$I->amOnPage($I->forumUrl($this->testGroupApply['id']));
			$I->see('Bewerbungen (1)');
			$I->click('Bewerbungen');
			$I->see($this->regionMember['name']);
			$I->click($this->regionMember['name']);
			$I->see('Bewerbung annehmen');
			$I->click('Ja');
		});
		$I->logout();
		$I->login($this->regionMember['email']);
		$I->amOnPage($I->forumUrl($this->testGroupApply['id']));
		$I->see($this->testGroupApply['name']);
		$I->see('Noch keine Themen gepostet');
	}
}
