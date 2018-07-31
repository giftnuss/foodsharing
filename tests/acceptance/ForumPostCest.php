<?php


class ForumPostCest
{
	public function _before(AcceptanceTester $I)
	{
		$this->testBezirk = 241;
		$this->createUsers($I);
		$this->createPosts($I);
	}

	public function _after(AcceptanceTester $I)
	{
	}

	private function createUsers($I)
	{
		$this->ambassador = $I->createAmbassador('pw', ['bezirk_id' => $this->testBezirk]);
		$this->foodsaver = $I->createFoodsaver('pw', ['bezirk_id' => $this->testBezirk]);
		$I->addBezirkAdmin($this->testBezirk, $this->ambassador['id']);
	}

	private function createPosts($I)
	{
		$this->thread_user_ambassador = $I->addForumTheme(241, $this->foodsaver['id'], false, ['time' => '2 hours ago']);
		$I->addForumThemePost($this->thread_user_ambassador['id'], $this->ambassador['id'], ['time' => '1 hour 45 minutes ago']);
		$this->thread_ambassador_user = $I->addForumTheme(241, $this->ambassador['id'], false, ['time' => '1 hour ago']);
		$I->addForumThemePost($this->thread_ambassador_user['id'], $this->foodsaver['id'], ['time' => '45 minutes ago']);
	}

	// tests

	/**
	 * @param AcceptanceTester $I
	 * @param \Codeception\Example $example
	 * @example["ambassador", "thread_ambassador_user", true]
	 * @example["foodsaver", "thread_ambassador_user", false]
	 * @example["ambassador", "thread_user_ambassador", true]
	 * @example["foodsaver", "thread_user_ambassador", false]
	 */
	public function SeePostButtonsAndClickFollowUnfollow(AcceptanceTester $I, \Codeception\Example $example)
	{
		$I->login($this->{$example[0]}['email'], 'pw');
		$I->amOnPage($I->forumThemeUrl($this->{$example[1]}['id'], null));
		$I->waitForActiveAPICalls();
		$this->waitForPostButtons($I, true, false, $example[2]);

		$followButton = \Codeception\Util\Locator::contains('.btn', 'folgen');
		$I->waitForElement($followButton);
		$I->click($followButton);
		$this->waitForPostButtons($I, false, false, $example[2]);

		$I->amOnPage($I->forumThemeUrl($this->{$example[1]}['id'], null));
		$I->waitForActiveAPICalls();

		$unfollowButton = \Codeception\Util\Locator::contains('.btn', 'entfolgen');
		$I->waitForElement($unfollowButton);
		$I->click($unfollowButton);
		$this->waitForPostButtons($I, false, false, $example[2]);
	}

	private function waitForPostButtons($I, $follow, $unfollow, $stickUnstick)
	{
		if ($follow) {
			$I->waitForText('Thema folgen', 3);
		}
		if ($unfollow) {
			$I->waitForText('Thema entfolgen', 3);
		}
		if ($stickUnstick) {
			$I->see('fixieren', '.btn');
		} else {
			$I->dontSee('fixieren', '.btn');
		}
	}

	public function StickUnstickPost(AcceptanceTester $I)
	{
		$I->login($this->ambassador['email'], 'pw');
		$I->amOnPage($I->forumThemeUrl($this->thread_user_ambassador['id'], null));
		$I->waitForActiveAPICalls();

		$nick = $I->haveFriend('nick');
		$nick->does(function (AcceptanceTester $I) {
			$I->login($this->foodsaver['email'], 'pw');
			$I->amOnPage($I->forumUrl($this->testBezirk));
			/* selector matches thread_user_ambassador after thread_ambassador_user */
			$title = $this->thread_user_ambassador['name'];
			$I->see($title, '#thread-' . $this->thread_ambassador_user['id'] . ' + #thread-' . $this->thread_user_ambassador['id']);
		});

		$stickButton = \Codeception\Util\Locator::contains('.btn', 'fixieren');
		$I->waitForElement($stickButton);
		$I->click($stickButton);
		$I->waitForElementNotVisible($stickButton);
		$nick->does(function (AcceptanceTester $I) {
			$I->wait(2);
			$I->amOnPage($I->forumUrl($this->testBezirk));
			$title = $this->thread_ambassador_user['name'];
			$I->see($title, '#thread-' . $this->thread_user_ambassador['id'] . ' + #thread-' . $this->thread_ambassador_user['id']);
		});

		$unstickButton = \Codeception\Util\Locator::contains('.btn', 'Fixierung aufheben');
		$I->waitForElementVisible($unstickButton);
		$I->click($unstickButton);
		$I->waitForElementNotVisible($unstickButton);
		$nick->does(function (AcceptanceTester $I) {
			$I->wait(2);
			$I->amOnPage($I->forumUrl($this->testBezirk));
			$title = $this->thread_user_ambassador['name'];
			$I->see($title, '#thread-' . $this->thread_ambassador_user['id'] . ' + #thread-' . $this->thread_user_ambassador['id']);
		});
	}
}
