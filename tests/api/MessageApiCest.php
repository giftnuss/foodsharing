<?php

class MessagesApiCest
{
	public function _before(\ApiTester $I)
	{
		$this->tester = $I;
		$this->user = $I->createFoodsaver();
	}

	/**
	 * @param ApiTester $I
	 */
	public function getAllConversations(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendGET('api/conversations');
		$I->seeResponseIsJson();
	}

	/**
	 * @param ApiTester $I
	 */
	public function getSingleConversation(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendGET('api/conversations/1');
		$I->seeResponseIsJson();
	}
}
