<?php

class ForumApiCest
{
	public function _before(\ApiTester $I)
	{
		$this->tester = $I;
		$this->user = $I->createFoodsaver();
		$this->region = $I->createRegion();
		$this->thread = $I->addForumTheme($this->region['id'], $this->user['id']);
	}

	/**
	 * @param ApiTester $I
	 */
	public function deleteNonExistingForumPostIs404(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendDELETE('api/forum/post/9999999');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
		$I->seeResponseIsJson();
	}

	/**
	 * @param ApiTester $I
	 */
	public function deleteOwnPostSucceeds(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendDELETE('api/forum/post/' . $this->thread['post']['id']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	/**
	 * @param ApiTester $I
	 */
	public function deleteForeignPostFails403(\ApiTester $I)
	{
		$foreigner = $I->createFoodsaver();
		$I->login($foreigner['email']);
		$I->sendDELETE('api/forum/post/' . $this->thread['post']['id']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
		$I->seeResponseIsJson();
	}
}
