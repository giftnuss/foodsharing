<?php

namespace api;

use ApiTester;
use Codeception\Util\HttpCode as Http;
use Faker;

class StoreRequestApiCest
{
	private $store;
	private $user;
	private $manager;
	private $region;
	private $faker;

	private const API_STORES = 'api/stores';

	public function _before(ApiTester $I)
	{
		$this->region = $I->createRegion();
		$this->store = $I->createStore($this->region['id']);
		$this->user = $I->createFoodsaver();
		$this->manager = $I->createStoreCoordinator(null, ['bezirk_id' => $this->region['id']]);
		$I->addStoreTeam($this->store['id'], $this->manager['id'], true);
		$this->faker = Faker\Factory::create('de_DE');
	}

	public function canAcceptStoreRequests(ApiTester $I): void
	{
		// create a request
		$user2 = $I->createFoodsaver();
		$this->createStoreRequest($I, $user2['id']);

		// accept request
		$I->login($this->manager['email']);
		$I->sendPatch(self::API_STORES . '/' . $this->store['id'] . '/requests/' . $user2['id']);
		$I->seeResponseCodeIs(Http::OK);

		// user should be in store and in store's region
		$I->seeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $user2['id'],
			'verantwortlich' => 0,
			'active' => 1,
		]);
		$I->seeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $user2['id'],
			'bezirk_id' => $this->store['bezirk_id'],
		]);
	}

	public function canOnlyAcceptStoreRequestsAsManager(ApiTester $I): void
	{
		// create a request
		$user2 = $I->createFoodsaver();
		$this->createStoreRequest($I, $user2['id']);

		// accept request
		$I->login($this->user['email']);
		$I->sendPatch(self::API_STORES . '/' . $this->store['id'] . '/requests/' . $user2['id']);
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		// user should not be active in store
		$I->seeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $user2['id'],
			'verantwortlich' => 0,
			'active' => 0,
		]);
	}

	public function canNotAcceptNonexistingRequests(ApiTester $I): void
	{
		$user2 = $I->createFoodsaver();

		// accept request
		$I->login($this->manager['email']);
		$I->sendPatch(self::API_STORES . '/' . $this->store['id'] . '/requests/' . $user2['id']);
		$I->seeResponseCodeIs(Http::NOT_FOUND);

		// user should not be in store
		$I->dontSeeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $user2['id'],
		]);
	}

	public function canRejectStoreRequests(ApiTester $I): void
	{
		// create a request
		$user2 = $I->createFoodsaver();
		$this->createStoreRequest($I, $user2['id']);

		// reject request
		$I->login($this->manager['email']);
		$I->sendDelete(self::API_STORES . '/' . $this->store['id'] . '/requests/' . $user2['id']);
		$I->seeResponseCodeIs(Http::OK);

		// user should not be in store and in store's region
		$I->dontSeeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $user2['id'],
		]);
	}

	public function canOnlyRejectStoreRequestsAsManager(ApiTester $I): void
	{
		// create a request
		$user2 = $I->createFoodsaver();
		$this->createStoreRequest($I, $user2['id']);

		// reject request
		$I->login($this->user['email']);
		$I->sendDelete(self::API_STORES . '/' . $this->store['id'] . '/requests/' . $user2['id']);
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		// user's request should still be there
		$I->seeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $user2['id'],
			'verantwortlich' => 0,
			'active' => 0,
		]);
	}

	public function canNotRejectNonexistingRequests(ApiTester $I): void
	{
		$user2 = $I->createFoodsaver();

		// reject request
		$I->login($this->manager['email']);
		$I->sendDelete(self::API_STORES . '/' . $this->store['id'] . '/requests/' . $user2['id']);
		$I->seeResponseCodeIs(Http::NOT_FOUND);

		// user should not be in store
		$I->dontSeeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $user2['id'],
		]);
	}

	private function createStoreRequest(ApiTester $I, int $userId): void
	{
		// create a request
		$I->haveInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $userId,
			'verantwortlich' => 0,
			'active' => 0,
		]);
	}
}
