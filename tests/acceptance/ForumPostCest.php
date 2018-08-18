<?php


class ForumPostCest
{
	private $ambassador;
	private $foodsaver;
	private $unverifiedFoodsaver;
	private $testBezirk;
	private $bigTestBezirk;
	private $moderatedTestBezirk;
	private $thread_user_ambassador;
	private $thread_ambassador_user;

	public function _before(AcceptanceTester $I)
	{
		$this->testBezirk = $I->createRegion();
		$this->bigTestBezirk = $I->createRegion(null, null, \Foodsharing\Modules\Core\DBConstants\Region\Type::BIG_CITY);
		$this->moderatedTestBezirk = $I->createRegion(null, null, \Foodsharing\Modules\Core\DBConstants\Region\Type::CITY, ['moderated' => true]);
		$this->createUsers($I);
		$this->createPosts($I);
	}

	public function _after(AcceptanceTester $I)
	{
	}

	private function createUsers(AcceptanceTester $I)
	{
		$this->ambassador = $I->createAmbassador(null, ['bezirk_id' => $this->testBezirk['id']]);
		$this->foodsaver = $I->createFoodsaver(null, ['bezirk_id' => $this->testBezirk['id']]);
		$this->unverifiedFoodsaver = $I->createFoodsaver(null, ['bezirk_id' => $this->testBezirk['id'], 'verified' => false]);
		$I->addBezirkAdmin($this->testBezirk['id'], $this->ambassador['id']);
		$I->addBezirkAdmin($this->bigTestBezirk['id'], $this->ambassador['id']);
		$I->addBezirkAdmin($this->moderatedTestBezirk['id'], $this->ambassador['id']);
		$I->addBezirkMember($this->bigTestBezirk['id'], $this->foodsaver['id']);
		$I->addBezirkMember($this->moderatedTestBezirk['id'], $this->foodsaver['id']);
	}

	private function createPosts(AcceptanceTester $I)
	{
		$this->thread_user_ambassador = $I->addForumTheme($this->testBezirk['id'], $this->foodsaver['id'], false, ['time' => '2 hours ago']);
		$I->addForumThemePost($this->thread_user_ambassador['id'], $this->ambassador['id'], ['time' => '1 hour 45 minutes ago']);
		$this->thread_ambassador_user = $I->addForumTheme($this->testBezirk['id'], $this->ambassador['id'], false, ['time' => '1 hour ago']);
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
		$I->login($this->{$example[0]}['email']);
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

	private function waitForPostButtons(AcceptanceTester $I, $follow, $unfollow, $stickUnstick)
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
		$I->login($this->ambassador['email']);
		$I->amOnPage($I->forumThemeUrl($this->thread_user_ambassador['id'], null));
		$I->waitForActiveAPICalls();

		$nick = $I->haveFriend('nick');
		$nick->does(function (AcceptanceTester $I) {
			$I->login($this->foodsaver['email']);
			$I->amOnPage($I->forumUrl($this->testBezirk['id']));
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
			$I->amOnPage($I->forumUrl($this->testBezirk['id']));
			$title = $this->thread_ambassador_user['name'];
			$I->see($title, '#thread-' . $this->thread_user_ambassador['id'] . ' + #thread-' . $this->thread_ambassador_user['id']);
		});

		$unstickButton = \Codeception\Util\Locator::contains('.btn', 'Fixierung aufheben');
		$I->waitForElementVisible($unstickButton);
		$I->click($unstickButton);
		$I->waitForElementNotVisible($unstickButton);
		$nick->does(function (AcceptanceTester $I) {
			$I->wait(2);
			$I->amOnPage($I->forumUrl($this->testBezirk['id']));
			$title = $this->thread_user_ambassador['name'];
			$I->see($title, '#thread-' . $this->thread_ambassador_user['id'] . ' + #thread-' . $this->thread_user_ambassador['id']);
		});
	}

	private function _createThread(AcceptanceTester $I, $regionId, $title)
	{
		$I->amOnPage($I->forumUrl($regionId));
		$I->click('Neues Thema verfassen');
		$I->fillField('#forum_create_thread_form_title', $title);
		$I->fillField('#forum_create_thread_form_body', 'TestThreadPost');
		$I->deleteAllMails();
		$I->click('Senden');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param \Codeception\Example $example
	 * @example["unverifiedFoodsaver", "testBezirk"]
	 * @example["foodsaver", "bigTestBezirk"]
	 * @example["foodsaver", "moderatedTestBezirk"]
	 */
	public function newThreadWillBeModerated(AcceptanceTester $I, \Codeception\Example $example)
	{
		$I->login($this->{$example[0]}['email']);
		$title = 'TestThreadTitle';
		$I->deleteAllMails();
		$this->_createThread($I, $this->{$example[1]}['id'], $title);
		$I->amOnPage($I->forumUrl($this->{$example[1]}['id']));
		$I->dontSee($title);
		$mail = $I->getMails()[0];
		$I->assertContains($title, $mail->text);
		$I->assertContains('tigt werden', $mail->subject);
	}

	public function newThreadCanBeActivated(AcceptanceTester $I)
	{
		$I->login($this->foodsaver['email']);
		$I->deleteAllMails();
		$title = 'moderated thread to be activated';
		$this->_createThread($I, $this->moderatedTestBezirk['id'], $title);
		$mail = $I->getMails()[0];
		$I->assertContains($title, $mail->text);
		$I->assertContains('tigt werden', $mail->subject);
		$I->assertRegExp('/http:\/\/.*bezirk.*&tid=[0-9]+/', $mail->html, 'mail should contain a link to thread');
		preg_match('/http:\/\/.*?\/(.*?)"/', $mail->html, $matches);
		$link = $matches[1];
		$I->deleteAllMails();
		$admin = $I->haveFriend('admin');
		$admin->does(function (AcceptanceTester $I) use ($link, $title) {
			$I->login($this->ambassador['email']);
			$I->amOnPage($link);
			$I->waitForActiveAPICalls();
			$I->see($title);
			$I->click('Thema aktivieren');
			$I->waitForActiveAPICalls();
		});
		$I->amOnPage($I->forumUrl($this->moderatedTestBezirk['id']));
		$I->see($title);
		/* There should have been notification mails - they are missing... */
		//$I->expectNumMails(3); /* Number of users in region, all should have gotten an email */
	}
}
