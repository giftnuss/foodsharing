<?php

namespace api;

use Codeception\Util\HttpCode as Http;

/**
 * Tests for the basket api.
 */
class StoreApiCest
{
	private $region_id = 241;
	private $user;
	private $store;

	private const API_STORES = 'api/stores';
	private const EMAIL = 'email';
	private const ID = 'id';

	public function _before(\ApiTester $I)
	{
		$this->user = $I->createFoodsaver();
		$this->store = $I->createStore($this->region_id);
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
		$I->login($this->user[self::EMAIL]);
		$newWallPost = ['text' => 'Lorem ipsum.'];
		$I->sendPOST(self::API_STORES . '/' . $this->store[self::ID] . '/posts', $newWallPost);

		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
		$I->seeInDatabase('fs_betrieb_notiz', ['text' => 'Lorem ipsum.']);
	}

	public function addStorePostReturns403IfNotLoggedIn(\ApiTester $I): void
	{
		$I->sendPOST(self::API_STORES . '/' . $this->store[self::ID] . '/posts', ['text' => 'Lorem ipsum.']);

		$I->seeResponseCodeIs(Http::FORBIDDEN);
	}
}
