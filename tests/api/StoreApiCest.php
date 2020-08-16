<?php

namespace api;

use Codeception\Util\HttpCode as Http;

/**
 * Tests for the basket api.
 */
class StoreApiCest
{
	private $user;
	private $store;
	private $region;

	private const API_STORES = 'api/stores';
	private const EMAIL = 'email';
	private const ID = 'id';

	public function _before(\ApiTester $I)
	{
		$this->user = $I->createFoodsaver();
		$this->region = $I->createRegion();
		$this->store = $I->createStore($this->region['id']);
	}

	public function getStore(\ApiTester $I)
	{
		$I->login($this->user[self::EMAIL]);
		$I->sendGET(self::API_STORES . '/' . $this->store[self::ID]);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
	}

	public function addStorePostPersistsStoreInDatabase(\ApiTester $I): void
	{
		$I->addStoreTeam($this->store[self::ID], $this->user[self::ID]);
		$I->login($this->user[self::EMAIL]);
		$newWallPost = ['text' => 'Lorem ipsum.'];
		$I->sendPOST(self::API_STORES . '/' . $this->store[self::ID] . '/posts', $newWallPost);

		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
		$I->seeInDatabase('fs_betrieb_notiz', ['text' => 'Lorem ipsum.']);
	}

	public function addStorePostReturns403IfNotInTeam(\ApiTester $I): void
	{
		$I->login($this->user[self::EMAIL]);

		$I->sendPOST(self::API_STORES . '/' . $this->store[self::ID] . '/posts', ['text' => 'Lorem ipsum.']);

		$I->seeResponseCodeIs(Http::FORBIDDEN);
	}

	public function addStorePostReturns403IfNotLoggedIn(\ApiTester $I): void
	{
		$I->sendPOST(self::API_STORES . '/' . $this->store[self::ID] . '/posts', ['text' => 'Lorem ipsum.']);

		$I->seeResponseCodeIs(Http::FORBIDDEN);
	}
}
