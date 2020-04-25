<?php

namespace api;

use Codeception\Util\HttpCode as Http;

/**
 * Tests for the user api.
 */
class UserApiCest
{
	private $user;
	private $userOrga;

	private const EMAIL = 'email';
	private const API_USER = 'api/user';
	private const ID = 'id';

	public function _before(\ApiTester $I)
	{
		$this->user = $I->createFoodsaver();
		$this->userOrga = $I->createOrga();
	}

	public function getUser(\ApiTester $I)
	{
		$testUser = $I->createFoodsaver();
		$I->login($this->user[self::EMAIL]);

		// see your own data
		$I->sendGET(self::API_USER . '/' . $this->user[self::ID]);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		$I->sendGET(self::API_USER . '/current');
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		// see someone else's data
		$I->sendGET(self::API_USER . '/' . $testUser[self::ID]);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		// do not see data of a non-existing user
		$I->sendGET(self::API_USER . '/999999999');
		$I->seeResponseCodeIs(Http::NOT_FOUND);
		$I->seeResponseIsJson();
	}

	public function getUserDetails(\ApiTester $I)
	{
		$testUser = $I->createFoodsaver();
		$I->login($this->user[self::EMAIL]);

		// see your own details
		$I->sendGET(self::API_USER . '/' . $this->user[self::ID] . '/details');
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		$I->sendGET(self::API_USER . '/current/details');
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		// do not see someone else's details unless you are orga
		$I->sendGET(self::API_USER . '/' . $testUser[self::ID] . '/details');
		$I->seeResponseCodeIs(Http::FORBIDDEN);
		$I->seeResponseIsJson();

		$I->login($this->userOrga[self::EMAIL]);
		$I->sendGET(self::API_USER . '/' . $testUser[self::ID] . '/details');
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		// do not see details of non-existing user
		$I->sendGET(self::API_USER . '/999999999/details');
		codecept_debug($I->grabResponse());
		$I->seeResponseCodeIs(Http::NOT_FOUND);
		$I->seeResponseIsJson();
	}
}
