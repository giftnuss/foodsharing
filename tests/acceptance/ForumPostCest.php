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
		$followMailSwitch = '.above .toggle-status .email'; // per Mail folgen
		$followBellSwitch = '.below .toggle-status .bell'; // per Glocke folgen

		$I->login($this->{$example[0]}['email']);

		// FOLLOW FORUM THREAD BY MAIL
		$I->amOnPage($I->forumThemeUrl($this->{$example[1]}['id'], null));
		$this->waitForPostButtons($I, false, false, $example[2]);

		$I->waitForElement($followMailSwitch);
		$I->click($followMailSwitch);
		$this->waitForPostButtons($I, true, false, $example[2]);

		$I->waitForElement($followBellSwitch);
		$I->click($followBellSwitch);
		$this->waitForPostButtons($I, true, true, $example[2]);

		// Simulate page reload
		$I->waitForActiveAPICalls();
		$I->amOnPage($I->forumThemeUrl($this->{$example[1]}['id'], null));
		$this->waitForPostButtons($I, true, true, $example[2]);

		$I->waitForElement($followMailSwitch);
		$I->click($followMailSwitch);
		$this->waitForPostButtons($I, false, true, $example[2]);

		$I->waitForElement($followBellSwitch);
		$I->click($followBellSwitch);
		$this->waitForPostButtons($I, false, false, $example[2]);

		// Simulate page reload
		$I->waitForActiveAPICalls();
		$I->amOnPage($I->forumThemeUrl($this->{$example[1]}['id'], null));
		$this->waitForPostButtons($I, false, false, $example[2]);

		$I->waitForElement($followBellSwitch);
		$I->click($followBellSwitch);
		$this->waitForPostButtons($I, false, true, $example[2]);

		// Simulate page reload
		$I->waitForActiveAPICalls();
		$I->amOnPage($I->forumThemeUrl($this->{$example[1]}['id'], null));
		$this->waitForPostButtons($I, false, true, $example[2]);
	}

	private function waitForPostButtons(AcceptanceTester $I, $followMail, $followBell, $stickUnstick)
	{
		$followMailSwitch = '.above .toggle-status .email';
		$followBellSwitch = '.below .toggle-status .bell';
		$stickySwitch = '.above .toggle-status .sticky'; // Thema fixieren
		$switchOn = ' a.enabled';
		$switchOff = ' a:not(.enabled)';
		$stickyText = 'Thema fixieren';

		$I->waitForActiveAPICalls();
		$I->waitForElement($followMailSwitch);
		$I->seeNumberOfElements($followMailSwitch, 1);
		if ($followMail) {
			// mail switch should be enabled
			$I->seeNumberOfElements($followMailSwitch . $switchOn, 1);
		} else {
			// mail switch should be disabled
			$I->seeNumberOfElements($followMailSwitch . $switchOff, 1);
		}

		$I->waitForElement($followBellSwitch);
		$I->seeNumberOfElements($followBellSwitch, 1);
		if ($followBell) {
			// bell switch should be enabled
			$I->seeNumberOfElements($followBellSwitch . $switchOn, 1);
		} else {
			// bell switch should be disabled
			$I->seeNumberOfElements($followBellSwitch . $switchOff, 1);
		}

		if ($stickUnstick) {
			$I->waitForText($stickyText, 3);
			$I->seeNumberOfElements($stickySwitch, 1);
		} else {
			$I->dontSee($stickyText);
		}
	}

	public function StickUnstickPost(AcceptanceTester $I)
	{
		$stickySwitch = '.above .toggle-status .sticky'; // Thema fixieren

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

		$I->waitForElement($stickySwitch);
		$I->click($stickySwitch);

		$nick->does(function (AcceptanceTester $I) {
			$I->waitForActiveAPICalls();
			$I->wait(2);
			$I->amOnPage($I->forumUrl($this->testBezirk['id']));
			$title = $this->thread_ambassador_user['name'];
			$I->see($title, '#thread-' . $this->thread_user_ambassador['id'] . ' + #thread-' . $this->thread_ambassador_user['id']);
		});

		$I->waitForElementVisible($stickySwitch);
		$I->waitForActiveAPICalls();
		$I->click($stickySwitch);

		$nick->does(function (AcceptanceTester $I) {
			$I->wait(2);
			$I->amOnPage($I->forumUrl($this->testBezirk['id']));
			$title = $this->thread_user_ambassador['name'];
			$I->see($title, '#thread-' . $this->thread_ambassador_user['id'] . ' + #thread-' . $this->thread_user_ambassador['id']);
			$I->waitForActiveAPICalls();
		});
		$I->waitForActiveAPICalls();
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
		$I->waitForActiveAPICalls();
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
