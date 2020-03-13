<?php

class ChatCest
{
	private $foodsaver1;
	private $foodsaver2;
	private $testBezirk;

	public function _before(AcceptanceTester $I)
	{
		$this->testBezirk = 241;
		$this->createUsers($I);
	}

	public function _after(AcceptanceTester $I)
	{
	}

	private function createUsers(AcceptanceTester $I)
	{
		$this->foodsaver1 = $I->createFoodsaver(null, ['bezirk_id' => $this->testBezirk]);
		$this->foodsaver2 = $I->createFoodsaver(null, ['bezirk_id' => $this->testBezirk]);
	}

	public function GetEmailNotificationForMessage(AcceptanceTester $I)
	{
		$I->login($this->foodsaver1['email']);
		// view the other users profile and start a chat
		$I->amOnPage('/profile/' . $this->foodsaver2['id']);
		$I->click('Nachricht schreiben');
		$I->waitForElementVisible('.chatboxtextarea', 15);

		// write a message to them
		$I->fillField('.chatboxtextarea', 'is anyone there for the email?');
		$I->pressKey('.chatboxtextarea', Facebook\WebDriver\WebDriverKeys::ENTER);
		$I->waitForText('is anyone there for', 20, '.chatboxcontent');

		$I->expectNumMails(1, 5);
		$mail = $I->getMails()[0];
		$I->assertStringContainsString('is anyone there for the email?', $mail->text);
		$I->assertStringContainsString($this->foodsaver1['name'], $mail->subject);
	}

	public function CanSendAndReceiveChatMessages(AcceptanceTester $I)
	{
		$I->login($this->foodsaver1['email']);

		// view the other users profile and start a chat
		$I->amOnPage('/profile/' . $this->foodsaver2['id']);
		$I->click('Nachricht schreiben');
		$I->waitForElementVisible('.chatboxtextarea', 15);

		// write a message to them
		$I->fillField('.chatboxtextarea', 'is anyone there?');
		$I->pressKey('.chatboxtextarea', Facebook\WebDriver\WebDriverKeys::ENTER);
		$I->waitForText('is anyone there?', 20, '.chatboxcontent');

		$I->seeInDatabase('fs_msg', [
			'foodsaver_id' => $this->foodsaver1['id'],
			'body' => 'is anyone there?'
		]);

		$matthias = $I->haveFriend('matthias');
		$matthias->does(function (AcceptanceTester $I) {
			$I->login($this->foodsaver2['email']);
			$I->amOnPage('/');

			$I->waitForActiveAPICalls();
			// check they have the nice little notification badge
			$I->see('1', '.topbar-messages .badge');

			// open the conversation menu and open the new conversation
			$I->click('.topbar-messages > a');
			$I->waitForElementVisible('.topbar-messages .list-group-item-warning', 4);
			$I->click('.topbar-messages .list-group-item-warning');
			$I->waitForElementVisible('.chatboxtextarea', 4);

			// write a nice reply
			$I->fillField('.chatboxtextarea', 'yes! I am here!');
			$I->pressKey('.chatboxtextarea', Facebook\WebDriver\WebDriverKeys::ENTER);
		});

		$I->waitForText('yes! I am here!', 10, '.chatboxcontent');

		$I->seeInDatabase('fs_msg', [
			'foodsaver_id' => $this->foodsaver2['id'],
			'body' => 'yes! I am here!'
		]);
	}
}
