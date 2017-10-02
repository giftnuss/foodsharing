<?php


class ChatCest
{
	public function _before(AcceptanceTester $I)
	{
		$this->testBezirk = 241;
		$this->createUsers($I);
	}

	public function _after(AcceptanceTester $I)
	{
	}

	private function createUsers($I)
	{
		$this->foodsaver1 = $I->createFoodsaver('pw', ['bezirk_id' => $this->testBezirk]);
		$this->foodsaver2 = $I->createFoodsaver('pw', ['bezirk_id' => $this->testBezirk]);
	}

	public function CanSendAndReceiveChatMessages(AcceptanceTester $I)
	{
		$I->login($this->foodsaver1['email'], 'pw');

		// view the other users profile and start a chat
		$I->amOnPage('/profile/' . $this->foodsaver2['id']);
		$I->click('Nachricht schreiben');
		$I->waitForElementVisible('.chatboxtextarea', 4);

		// write a message to them
		$I->fillField('.chatboxtextarea', 'is anyone there?');
		$I->pressKey('.chatboxtextarea', WebDriverKeys::ENTER);
		$I->wait(1);

		$I->see('is anyone there?', '.chatboxcontent');

		$I->seeInDatabase('fs_msg', [
			'foodsaver_id' => $this->foodsaver1['id'],
			'body' => 'is anyone there?'
		]);

		$matthias = $I->haveFriend('matthias');
		$matthias->does(function (AcceptanceTester $I) {
			$I->login($this->foodsaver2['email'], 'pw');
			$I->amOnPage('/');

			// check they have the nice little notification badge
			$I->see('1', '.msg .badge');

			// open the conversation menu and open the new conversation
			$I->click('.msg a');
			$I->waitForElementVisible('.unread-1', 4);
			$I->click('.unread-1 a');
			$I->waitForElementVisible('.chatboxtextarea', 4);

			// write a nice reply
			$I->fillField('.chatboxtextarea', 'yes! I am here!');
			$I->pressKey('.chatboxtextarea', WebDriverKeys::ENTER);
			$I->wait(1);
		});

		$I->see('yes! I am here!', '.chatboxcontent');

		$I->seeInDatabase('fs_msg', [
			'foodsaver_id' => $this->foodsaver2['id'],
			'body' => 'yes! I am here!'
		]);
	}
}
