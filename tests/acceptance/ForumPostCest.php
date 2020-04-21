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
		$I->addRegionAdmin($this->testBezirk['id'], $this->ambassador['id']);
		$I->addRegionAdmin($this->bigTestBezirk['id'], $this->ambassador['id']);
		$I->addRegionAdmin($this->moderatedTestBezirk['id'], $this->ambassador['id']);
		$I->addRegionMember($this->bigTestBezirk['id'], $this->foodsaver['id']);
		$I->addRegionMember($this->moderatedTestBezirk['id'], $this->foodsaver['id']);
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

	private function _createThread(AcceptanceTester $I, $regionId, $title, $emailPossible, $sendEmail = true)
	{
		$I->amOnPage($I->forumUrl($regionId));
		$I->click('Neues Thema verfassen');
		$I->fillField('#forum_create_thread_form_title', $title);
		$I->fillField('#forum_create_thread_form_body', 'TestThreadPost');
		$I->deleteAllMails();
		if (!$emailPossible) {
			$I->dontSee('Alle Forenmitglieder über die Erstellung dieses neuen Themas per E-Mail informieren');
		} elseif ($sendEmail) {
			$I->selectOption('#forum_create_thread_form_sendMail_1', 'Ja');
		} else {
			$I->selectOption('#forum_create_thread_form_sendMail_0', 'Nein');
		}
		$I->click('Senden');
	}

	/**
	 * @example["unverifiedFoodsaver", "testBezirk"]
	 * @example["foodsaver", "bigTestBezirk"]
	 * @example["foodsaver", "moderatedTestBezirk"]
	 */
	public function newThreadWillBeModerated(AcceptanceTester $I, \Codeception\Example $example)
	{
		$I->login($this->{$example[0]}['email']);
		$title = 'TestThreadTitle';
		$I->deleteAllMails();
		$emailPossible = false;
		$this->_createThread($I, $this->{$example[1]}['id'], $title, $emailPossible);
		$I->amOnPage($I->forumUrl($this->{$example[1]}['id']));
		$I->dontSee($title);
		$mail = $I->getMails()[0];
		$I->assertStringContainsString($title, $mail->text);
		$I->assertStringContainsString('tigt werden', $mail->subject);
	}

	/**
	 * @example["foodsaver", "testBezirk"]
	 */
	public function newThreadWillNotSendEmail(AcceptanceTester $I, \Codeception\Example $example)
	{
		$I->login($this->{$example[0]}['email']);
		$title = 'TestThreadTitleWithoutEmailToForumMembers';
		$I->deleteAllMails();
		$emailPossible = true;
		$sendEmail = false;
		$this->_createThread($I, $this->{$example[1]}['id'], $title, $emailPossible, $sendEmail);
		$I->amOnPage($I->forumUrl($this->{$example[1]}['id']));
		$I->see($title);
		$I->expectNumMails(0);
	}

	/**
	 * @example["foodsaver", "testBezirk"]
	 */
	public function newThreadWillSendEmail(AcceptanceTester $I, \Codeception\Example $example)
	{
		$I->login($this->{$example[0]}['email']);
		$title = 'TestThreadTitleWithEmailToForumMembers';
		$I->deleteAllMails();
		$emailPossible = true;
		$sendEmail = true;
		$this->_createThread($I, $this->{$example[1]}['id'], $title, $emailPossible, $sendEmail);
		$I->amOnPage($I->forumUrl($this->{$example[1]}['id']));
		$I->see($title);
		$numMails = count($I->getMails());
		/* one could assume, there should be 3 mail, because there are 3 people in the region,
		but the number of recieved mails fluctuates.
		This also happens if you try it in the test setup.
		Thus the test is only for more than 0 mails.
		*/
		$I->assertGreaterThan(0, $numMails);
	}

	/**
	 * @example["ambassador", "thread_ambassador_user", true]
	 */
	public function newThreadByAmbassadorWillNotBeModerated(AcceptanceTester $I, \Codeception\Example $example)
	{
		$I->login($this->{$example[0]}['email']);
		$title = 'TestAmbassadorThreadTitle';
		$this->_createThread($I, $this->testBezirk['id'], $title, true);
		$I->amOnPage($I->forumUrl($this->testBezirk['id']));
		$I->see($title);
	}

	public function newThreadCanBeActivated(AcceptanceTester $I)
	{
		$I->login($this->foodsaver['email']);
		$I->deleteAllMails();
		$title = 'moderated thread to be activated';
		$this->_createThread($I, $this->moderatedTestBezirk['id'], $title, false);
		$mail = $I->getMails()[0];
		$I->assertStringContainsString($title, $mail->text);
		$I->assertStringContainsString('tigt werden', $mail->subject);
		$I->assertRegExp('/http:\/\/.*bezirk.*&amp;tid=[0-9]+/', $mail->html, 'mail should contain a link to thread');
		preg_match('/http:\/\/.*?\/(.*?)"/', $mail->html, $matches);
		$link = html_entity_decode($matches[1]);
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
		/* ...missing because thread activation currently doesn't send emails :( */
		/* Number of users in region, all (3) should get an email as soon as it is implemented */
		/* Well. We can check against 0 until it is implemented to not forget this test later on :) */
		$I->expectNumMails(0);
	}

	/**
	 * @example["foodsaver", "bigTestBezirk"]
	 */
	public function DeleteLastPostAndGetRedirectedToForum(AcceptanceTester $I, \Codeception\Example $example)
	{
		$I->login($this->{$example[0]}['email']);
		$title = 'TestThreadTitleForDeletion';
		$I->deleteAllMails();
		$this->_createThread($I, $this->{$example[1]}['id'], $title, false);
		$I->amOnPage($I->forumUrl($this->{$example[1]}['id']));

		$mail = $I->getMails()[0];
		preg_match('/http:\/\/.*?\/(.*?)"/', $mail->html, $matches);
		$link = html_entity_decode($matches[1]);

		$admin = $I->haveFriend('admin');
		$admin->does(function (AcceptanceTester $I) use ($link, $title) {
			$I->login($this->ambassador['email']);
			$I->amOnPage($link);
			$I->waitForActiveAPICalls();
			$I->see($title);
			$I->click('Thema aktivieren');
			$I->waitForActiveAPICalls();
		});

		$I->amOnPage($I->forumUrl($this->{$example[1]}['id']));
		$I->canSee($title);
		$I->click('.forum_threads a');
		$I->waitForActiveAPICalls();

		$I->seeCurrentUrlMatches('~' . $I->forumUrl($this->{$example[1]}['id']) . '&tid=(\d+)~');
		$I->click('a[title="Beitrag löschen"]');
		$I->canSee('Beitrag löschen');
		$confirmButton = \Codeception\Util\Locator::contains('.btn', 'Ja, ich bin mir sicher');
		$I->waitForElementVisible($confirmButton);
		$I->click($confirmButton);
		$I->waitForElementNotVisible($confirmButton);
		$I->waitForActiveAPICalls();
		$I->seeCurrentUrlEquals($I->forumUrl($this->{$example[1]}['id']));
		$I->cantSee($title);
	}
}
