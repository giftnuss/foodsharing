<?php

class WallPostCest
{
	private $regionMember;
	private $unconnectedFoodsaver;
	private $testGroup;

	public function _before(AcceptanceTester $I)
	{
		$this->testGroup = $I->createWorkingGroup('a top group');
		$this->regionMember = $I->createFoodsaver();
		$I->addRegionMember($this->testGroup['id'], $this->regionMember['id']);
		$this->unconnectedFoodsaver = $I->createFoodsaver();
	}

	public function _after(AcceptanceTester $I)
	{
	}

	// tests

	/**
	 * @example["regionMember", true]
	 * @example["unconnectedFoodsaver", false]
	 */
	public function canAddSeeWallPosts(AcceptanceTester $I, \Codeception\Example $example)
	{
		$I->login($this->{$example[0]}['email']);
		$I->amOnPage($I->regionWallUrl($this->testGroup['id']));
		if ($example[1]) {
			$I->see('Pinnwand');
			$wallPostText = 'Hey there, this is my new wallpost!';
			$I->fillField('#wallpost-text', $wallPostText);
			$I->click('Senden');
			$I->waitForElement('.bpost');
			$I->see($wallPostText);
			$I->seeInDatabase('fs_wallpost', ['body' => $wallPostText, 'foodsaver_id' => $this->{$example[0]}['id']]);
		} else {
			$I->dontSee('Pinnwand');
		}
	}

	/**
	 * @param \Codeception\Example $example
	 */
	public function cannotAddEmptyWallPost(AcceptanceTester $I)
	{
		$I->login($this->regionMember['email']);
		$I->amOnPage($I->regionWallUrl($this->testGroup['id']));
		$I->fillField('#wallpost-text', '');
		$I->click('Senden');
		$I->waitForPageBody();
		$I->dontSeeInDatabase('fs_wallpost', ['body' => '', 'foodsaver_id' => $this->regionMember['id']]);
	}
}
