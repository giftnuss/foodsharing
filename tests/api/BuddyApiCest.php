<?php

namespace Foodsharing\api;

use ApiTester;
use Codeception\Util\HttpCode;
use Foodsharing\Modules\Core\DBConstants\Buddy\BuddyId;

class BuddyApiCest
{
	private $user1;
	private $user2;

	public function _before(ApiTester $I)
	{
		$this->user1 = $I->createFoodsharer();
		$this->user2 = $I->createFoodsharer();
	}

	public function canOnlySendBuddyRequestWhenLoggedIn(ApiTester $I)
	{
		$I->sendPUT('api/buddy/' . $this->user2['id']);
		$I->seeResponseCodeIs(HttpCode::FORBIDDEN);

		$I->login($this->user1['email']);
		$I->sendPUT('api/buddy/' . $this->user2['id']);
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeInDatabase('fs_buddy', [
			'foodsaver_id' => $this->user1['id'],
			'buddy_id' => $this->user2['id'],
			'confirmed' => BuddyId::REQUESTED
		]);
	}

	public function canAcceptBuddyRequest(ApiTester $I)
	{
		$I->login($this->user1['email']);
		$I->sendPUT('api/buddy/' . $this->user2['id']);
		$I->seeResponseCodeIs(HttpCode::OK);

		$I->login($this->user2['email']);
		$I->sendPUT('api/buddy/' . $this->user1['id']);
		$I->seeResponseCodeIs(HttpCode::OK);

		$I->seeInDatabase('fs_buddy', [
			'foodsaver_id' => $this->user1['id'],
			'buddy_id' => $this->user2['id'],
			'confirmed' => BuddyId::BUDDY
		]);
		$I->seeInDatabase('fs_buddy', [
			'foodsaver_id' => $this->user2['id'],
			'buddy_id' => $this->user1['id'],
			'confirmed' => BuddyId::BUDDY
		]);
	}

	public function buddyRequestIsOverwritten(ApiTester $I)
	{
		$I->login($this->user1['email']);
		$I->sendPUT('api/buddy/' . $this->user2['id']);
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeInDatabase('fs_buddy', [
			'foodsaver_id' => $this->user1['id'],
			'buddy_id' => $this->user2['id'],
			'confirmed' => BuddyId::REQUESTED
		]);

		$I->sendPUT('api/buddy/' . $this->user2['id']);
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeInDatabase('fs_buddy', [
			'foodsaver_id' => $this->user1['id'],
			'buddy_id' => $this->user2['id'],
			'confirmed' => BuddyId::REQUESTED
		]);
	}

	public function canNotSendRequestToBuddy(ApiTester $I)
	{
		$I->haveInDatabase('fs_buddy', [
			'foodsaver_id' => $this->user1['id'],
			'buddy_id' => $this->user2['id'],
			'confirmed' => BuddyId::BUDDY
		]);
		$I->haveInDatabase('fs_buddy', [
			'foodsaver_id' => $this->user2['id'],
			'buddy_id' => $this->user1['id'],
			'confirmed' => BuddyId::BUDDY
		]);

		$I->login($this->user1['email']);
		$I->sendPUT('api/buddy/' . $this->user2['id']);
		$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

		$I->login($this->user2['email']);
		$I->sendPUT('api/buddy/' . $this->user1['id']);
		$I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
	}
}
