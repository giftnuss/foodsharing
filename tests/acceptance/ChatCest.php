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

	public function CanSendAChatMessage(AcceptanceTester $I)
	{
		$I->login($this->foodsaver1['email'], 'pw');
		$I->amOnPage('/profile/'.$this->foodsaver2['id']);
		$I->click('Nachricht schreiben');
		$I->waitForElementVisible('.chatboxtextarea', 4);
		$I->fillField('.chatboxtextarea', 'heya');
		$I->pressKey('.chatboxtextarea',WebDriverKeys::ENTER);
		$I->seeInDatabase('fs_msg', [
			'foodsaver_id' => $this->foodsaver1['id'],
			'body' => 'heya'
		]);
	}
}
