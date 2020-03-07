<?php

namespace api;

use Codeception\Util\HttpCode as Http;

/**
 * Tests for the user api.
 */
class UserApiCest
{
	private $user;

	private const EMAIL = 'email';
	private const API_USER = 'api/user';
	private const ID = 'id';

	public function _before(\ApiTester $I)
	{
		$this->user = $I->createFoodsaver();
	}

	public function getUser(\ApiTester $I)
	{
		$testUser = $I->createFoodsaver();
		$I->login($this->user[self::EMAIL]);
		$I->sendGET(self::API_USER . '/' . $testUser[self::ID]);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		$I->login($this->user[self::EMAIL]);
		$I->sendGET(self::API_USER . '/999999999');
		$I->seeResponseCodeIs(Http::NOT_FOUND);
		$I->seeResponseIsJson();
	}
}
