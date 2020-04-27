<?php

class RegionApiCest
{
	private $user;
	private $region;

	public function _before(\ApiTester $I)
	{
		$this->tester = $I;
		$this->user = $I->createFoodsaver();
		$this->region = $I->createRegion();
	}

	public function canJoinRegion(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function joinNotExistingRegionIs404(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/999999999/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
		$I->seeResponseIsJson();
	}

	public function canNotJoinRegionAsFoodsharer(\ApiTester $I)
	{
		$foodsharer = $I->createFoodsharer();
		$I->login($foodsharer['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
		$I->seeResponseIsJson();
	}

	public function canJoinRegionTwice(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function canNotLeaveRegionWithoutLogin(\ApiTester $I)
	{
		$I->sendPOST('api/region/' . $this->region['id'] . '/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
		$I->seeResponseIsJson();
	}

	public function canLeaveRegionWithoutJoiningFirst(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function canLeaveRegion(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();

		$I->login($this->user['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function canNotLeaveDifferentRegionThanJoined(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->sendPOST('api/region/999999/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
		$I->seeResponseIsJson();
	}
}
