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
		$I->addBezirkMember($this->testBezirk, $this->ambassador['id'], true);
		$I->addBezirkMember($this->testBezirk, $this->foodsaver['id']);
	}

	private function createPosts($I)
	{
		$this->thread_user_ambassador_id = $I->addForumTheme(241, $this->foodsaver['id'], 'Theme B', 'Text B1', '2 hours ago');
		$I->addForumThemePost($this->thread_user_ambassador_id, $this->ambassador['id'], 'Text B2', '1 hour 45 minutes ago');
		$this->thread_ambassador_user_id = $I->addForumTheme(241, $this->ambassador['id'], 'Theme A', 'Text A1', '1 hour ago');
		$I->addForumThemePost($this->thread_ambassador_user_id, $this->foodsaver['id'], 'Text A2', '45 minutes ago');
	}

    // tests
	/**
	 * @param AcceptanceTester $I
	 * @param \Codeception\Example $example
	 * @example["ambassador", "thread_ambassador_user_id", true]
	 * @example["foodsaver", "thread_ambassador_user_id", false]
	 * @example["ambassador", "thread_user_ambassador_id", true]
	 * @example["foodsaver", "thread_user_ambassador_id", false]
	 */
    public function SeePostButtonsAndClickFollowUnfollow(AcceptanceTester $I, \Codeception\Example $example)
    {
    	$I->login($this->{$example[0]}['email'], 'pw');
    	$I->amOnPage($I->forumThemeUrl($this->{$example[1]}, null));
		$I->waitForPageBody();
		$this->expectPostButtons($I, true, $example[2]);

		$I->click('a.button.bt_follow');
		/* currently, this does a XHR request after which a full page reload is done. Click does not wait for the XHR
		   to be completed so we need some additional wait before we can detect a page reload...
		*/
		$I->wait(1);
		$I->waitForPageBody();
		$this->expectPostButtons($I, false, $example[2]);

		$I->click('a.button.bt_unfollow');
		$I->wait(1);
		$I->waitForPageBody();
		$this->expectPostButtons($I, true, $example[2]);
    }

    private function expectPostButtons($I, $follow, $stick)
	{
		if($follow)
		{
			$I->see("folgen", 'a.button.bt_follow');
			$I->dontSee("entfolgen", 'a.button.bt_unfollow');
		} else {
			$I->see("entfolgen", 'a.button.bt_unfollow');
			$I->dontSee("folgen", 'a.button.bt_follow');
		}
		if($stick)
		{
			$I->see("fixieren", 'a.button.bt_stick');
		} else {
			$I->dontSee("fixieren", 'a.button.bt_stick');
		}
	}

	public function StickUnstickPost(AcceptanceTester $I)
	{
		$I->login($this->ambassador['email'], 'pw');
		$I->amOnPage($I->forumThemeUrl($this->thread_user_ambassador_id, null));
		$nick = $I->haveFriend('nick');
		$nick->does(function(AcceptanceTester $I)
		{
			$I->login($this->foodsaver['email'], 'pw');
			$I->amOnPage($I->forumUrl($this->testBezirk));
			/* selector matches Theme B after Theme A */
			$I->see("Theme B", '#thread-'.$this->thread_ambassador_user_id.' + #thread-'.$this->thread_user_ambassador_id);
		});

		$I->click('a.button.bt_stick');
		$I->wait(1);
		$nick->does(function(AcceptanceTester $I)
		{
			$I->amOnPage($I->forumUrl($this->testBezirk));
			$I->see("Theme A", '#thread-'.$this->thread_user_ambassador_id.' + #thread-'.$this->thread_ambassador_user_id);
		});

		$I->waitForPageBody();

		$I->click('a.button.bt_unstick');
		$I->wait(1);
		$nick->does(function(AcceptanceTester $I)
		{
			$I->amOnPage($I->forumUrl($this->testBezirk));
			$I->see("Theme B", '#thread-'.$this->thread_ambassador_user_id.' + #thread-'.$this->thread_user_ambassador_id);
		});
	}
}
