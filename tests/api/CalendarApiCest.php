<?php

namespace Foodsharing\api;

use ApiTester;
use Codeception\Util\HttpCode;

class CalendarApiCest
{
	private const TEST_TOKEN = '1234567890';
	private $user;

	public function _before(ApiTester $I)
	{
		$this->user = $I->createFoodsharer();
	}

	public function canNotAccessApiWithoutLogin(ApiTester $I)
	{
		$I->sendGet('api/calendar/token');
		$I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

		$I->sendPut('api/calendar/token');
		$I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

		$I->sendDelete('api/calendar/token');
		$I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
	}

	public function canRequestExistingToken(ApiTester $I)
	{
		$I->login($this->user['email']);

		$I->sendGet('api/calendar/token');
		$I->seeResponseCodeIs(HttpCode::NOT_FOUND);

		$I->haveInDatabase('fs_apitoken', [
			'foodsaver_id' => $this->user['id'],
			'token' => self::TEST_TOKEN
		]);
		$I->sendGet('api/calendar/token');
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['token' => self::TEST_TOKEN]);
	}

	public function canCreateToken(ApiTester $I)
	{
		$I->login($this->user['email']);

		// create a token
		$I->sendPut('api/calendar/token');
		$I->seeResponseCodeIs(HttpCode::OK);
		$token1 = json_decode($I->grabResponse(), true)['token'];

		// check if the token was set
		$I->seeInDatabase('fs_apitoken', [
			'foodsaver_id' => $this->user['id'],
			'token' => $token1
		]);

		// create a new token
		$I->sendPut('api/calendar/token');
		$I->seeResponseCodeIs(HttpCode::OK);
		$token2 = json_decode($I->grabResponse(), true)['token'];

		// check if the token was overwritten
		$I->seeInDatabase('fs_apitoken', [
			'foodsaver_id' => $this->user['id'],
			'token' => $token2
		]);
		$I->dontSeeInDatabase('fs_apitoken', [
			'foodsaver_id' => $this->user['id'],
			'token' => $token1
		]);
	}

	public function canDeleteToken(ApiTester $I)
	{
		$I->haveInDatabase('fs_apitoken', [
			'foodsaver_id' => $this->user['id'],
			'token' => self::TEST_TOKEN
		]);

		$I->login($this->user['email']);
		$I->sendDelete('api/calendar/token');
		$I->dontSeeInDatabase('fs_apitoken', [
			'foodsaver_id' => $this->user['id']
		]);
	}

	public function canRequestCalendar(ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->sendGet('api/calendar/' . self::TEST_TOKEN);
		$I->seeResponseCodeIs(HttpCode::FORBIDDEN);

		$I->haveInDatabase('fs_apitoken', [
			'foodsaver_id' => $this->user['id'],
			'token' => self::TEST_TOKEN
		]);
		$I->sendGet('api/calendar/' . self::TEST_TOKEN);
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContains('BEGIN:VCALENDAR');
	}
}
