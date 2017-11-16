<?php

class WorkGroupCest
{
	private $regionMember;
	private $unconnectedFoodsaver;
	private $testGroup;

	public function _before(AcceptanceTester $I)
	{
		$this->testGroup = $I->createWorkingGroup('the group for testing to see groups');
		$this->regionMember = $I->createFoodsaver();
		$I->addBezirkMember($this->testGroup['id'], $this->regionMember['id']);
		$this->unconnectedFoodsaver = $I->createFoodsaver();
		$this->foodsharer = $I->createFoodsharer();
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
}
