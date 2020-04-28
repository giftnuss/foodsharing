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
		$I->dontSeeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $this->user['id'],
			'bezirk_id' => $this->region['id']
			]);
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->seeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $this->user['id'],
			'bezirk_id' => $this->region['id']
			]);
	}

	public function joinNotExistingRegionIs404(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/999999999/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
		$I->seeResponseIsJson();
		$I->dontSeeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $this->user['id'],
			'bezirk_id' => 999999999
			]);
	}

	public function canNotJoinRegionAsFoodsharer(\ApiTester $I)
	{
		$foodsharer = $I->createFoodsharer();
		$I->login($foodsharer['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
		$I->seeResponseIsJson();
		$I->dontSeeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $this->user['id'],
			'bezirk_id' => $this->region['id']
			]);
	}

	public function canJoinRegionTwice(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		// database entry is not interesting, already tested that in other test
	}

	public function canNotLeaveRegionWithoutLogin(\ApiTester $I)
	{
		$I->sendPOST('api/region/' . $this->region['id'] . '/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
		$I->seeResponseIsJson();
		// cannot test whether leaving did not change database since
		// there is no user to look at
	}

	public function canLeaveRegionWithoutJoiningFirst(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->dontSeeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $this->user['id'],
			'bezirk_id' => $this->region['id']
			]);
	}

	public function canLeaveRegion(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

		$I->login($this->user['email']);
		// second login necessary since the list of regions of the current
		// user are saved in the session and not updated there by the
		// join request. So without relogin the leave would fail.
		$I->sendPOST('api/region/' . $this->region['id'] . '/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->dontSeeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $this->user['id'],
			'bezirk_id' => $this->region['id']
			]);
	}

	public function canNotLeaveDifferentRegionThanJoined(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendPOST('api/region/' . $this->region['id'] . '/join');
		$I->sendPOST('api/region/999999/leave');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
		$I->seeResponseIsJson();
		$I->seeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $this->user['id'],
			'bezirk_id' => $this->region['id']
			]);
	}
}
