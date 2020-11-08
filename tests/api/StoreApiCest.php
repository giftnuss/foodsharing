<?php

namespace api;

use ApiTester;
use Codeception\Util\HttpCode as Http;
use Faker;
use Foodsharing\Modules\Core\DBConstants\Store\Milestone;
use Foodsharing\Modules\Core\DBConstants\Store\StoreLogAction;

/**
 * Tests for the store api.
 */
class StoreApiCest
{
	private $store;
	private $user;
	private $manager;
	private $region;
	private $faker;

	private const API_STORES = 'api/stores';
	private const EMAIL = 'email';
	private const ID = 'id';

	public function _before(ApiTester $I)
	{
		$this->region = $I->createRegion();
		$this->store = $I->createStore($this->region['id']);
		$this->user = $I->createFoodsaver();
		$this->manager = $I->createStoreCoordinator(null, ['bezirk_id' => $this->region['id']]);
		$I->addStoreTeam($this->store[self::ID], $this->manager[self::ID], true);
		$this->faker = Faker\Factory::create('de_DE');
	}

	public function getStore(ApiTester $I)
	{
		$I->login($this->user[self::EMAIL]);
		$I->sendGET(self::API_STORES . '/' . $this->store[self::ID]);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
	}

	public function canWriteStoreWallpostAndGetAllPosts(ApiTester $I): void
	{
		$I->addStoreTeam($this->store[self::ID], $this->user[self::ID], false);
		$I->login($this->user[self::EMAIL]);
		$newWallPost = $this->faker->realText(200);
		$I->sendPOST(self::API_STORES . '/' . $this->store[self::ID] . '/posts', ['text' => $newWallPost]);

		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
		$I->seeInDatabase('fs_betrieb_notiz', [
			'foodsaver_id' => $this->user[self::ID],
			'betrieb_id' => $this->store[self::ID],
			'text' => $newWallPost,
		]);

		$I->sendGET(self::API_STORES . '/' . $this->store[self::ID] . '/posts');

		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['text' => $newWallPost]);
	}

	public function noStoreWallIfNotInTeam(ApiTester $I): void
	{
		$I->login($this->user[self::EMAIL]);

		$I->sendGET(self::API_STORES . '/' . $this->store[self::ID] . '/posts');

		$I->seeResponseCodeIs(Http::FORBIDDEN);

		$I->sendPOST(self::API_STORES . '/' . $this->store[self::ID] . '/posts', ['text' => 'Lorem ipsum.']);

		$I->seeResponseCodeIs(Http::FORBIDDEN);
	}

	public function noStoreWallIfNotLoggedIn(ApiTester $I): void
	{
		$I->sendGET(self::API_STORES . '/' . $this->store[self::ID] . '/posts');

		$I->seeResponseCodeIs(Http::UNAUTHORIZED);

		$I->sendPOST(self::API_STORES . '/' . $this->store[self::ID] . '/posts', ['text' => 'Lorem ipsum.']);

		$I->seeResponseCodeIs(Http::UNAUTHORIZED);
	}

	/**
	 * All team members can remove their own posts at any time.
	 */
	public function canRemoveOwnStorePost(ApiTester $I): void
	{
		$wallPost = [
			'betrieb_id' => $this->store[self::ID],
			'foodsaver_id' => $this->user[self::ID],
			'text' => $this->faker->realText(100),
			'zeit' => $this->faker->dateTimeBetween('-14 days', '-30m')->format('Y-m-d H:i:s'),
			'milestone' => Milestone::NONE,
		];
		$postId = $I->haveInDatabase('fs_betrieb_notiz', $wallPost);

		$I->addStoreTeam($this->store[self::ID], $this->user[self::ID], false);
		$I->login($this->user[self::EMAIL]);

		$I->sendDELETE(self::API_STORES . '/' . $this->store[self::ID] . '/posts/' . $postId);

		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
		$I->dontSeeInDatabase('fs_betrieb_notiz', ['id' => $postId]);

		$I->seeInDatabase('fs_store_log', [
			'store_id' => $this->store[self::ID],
			'fs_id_a' => $this->user[self::ID],
			'fs_id_p' => $this->user[self::ID],
			'content' => $wallPost['text'],
			'date_reference' => $wallPost['zeit'],
			'action' => StoreLogAction::DELETED_FROM_WALL,
		]);
	}

	/**
	 * Store managers can remove posts by others if they are older than 1 month.
	 */
	public function storeManagerCanRemoveOldStorePost(ApiTester $I): void
	{
		$wallPost = [
			'betrieb_id' => $this->store[self::ID],
			'foodsaver_id' => $this->user[self::ID],
			'text' => $this->faker->realText(100),
			'zeit' => $this->faker->dateTimeBetween('-66 days', '-33 days')->format('Y-m-d H:i:s'),
			'milestone' => Milestone::NONE,
		];
		$postId = $I->haveInDatabase('fs_betrieb_notiz', $wallPost);

		$I->login($this->manager[self::EMAIL]);

		$I->sendDELETE(self::API_STORES . '/' . $this->store[self::ID] . '/posts/' . $postId);

		$I->seeResponseCodeIs(Http::OK);
		$I->dontSeeInDatabase('fs_betrieb_notiz', ['id' => $postId]);

		$I->seeInDatabase('fs_store_log', [
			'store_id' => $this->store[self::ID],
			'fs_id_a' => $this->manager[self::ID],
			'fs_id_p' => $this->user[self::ID],
			'content' => $wallPost['text'],
			'date_reference' => $wallPost['zeit'],
			'action' => StoreLogAction::DELETED_FROM_WALL,
		]);
	}

	public function storeManagerCannotRemoveNewStorePost(ApiTester $I): void
	{
		$wallPost = [
			'betrieb_id' => $this->store[self::ID],
			'foodsaver_id' => $this->user[self::ID],
			'text' => $this->faker->realText(100),
			'zeit' => $this->faker->dateTimeBetween('-14 days', '-30m')->format('Y-m-d H:i:s'),
			'milestone' => Milestone::NONE,
		];
		$postId = $I->haveInDatabase('fs_betrieb_notiz', $wallPost);

		$I->login($this->manager[self::EMAIL]);

		$I->sendDELETE(self::API_STORES . '/' . $this->store[self::ID] . '/posts/' . $postId);

		$I->seeResponseCodeIs(Http::FORBIDDEN);
		$I->seeInDatabase('fs_betrieb_notiz', ['id' => $postId]);
	}

	public function canAcceptStoreRequests(ApiTester $I): void
	{
		// create a request
		$user2 = $I->createFoodsaver();
		$this->createStoreRequest($I, $user2['id']);

		// accept request
		$I->login($this->manager[self::EMAIL]);
		$I->sendPatch(self::API_STORES . '/' . $this->store['id'] . '/requests/' . $user2['id']);
		$I->seeResponseCodeIs(Http::OK);

		// user should be in store and in store's region
		$I->seeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $user2['id'],
			'verantwortlich' => 0,
			'active' => 1
		]);
		$I->seeInDatabase('fs_foodsaver_has_bezirk', [
			'foodsaver_id' => $user2['id'],
			'bezirk_id' => $this->store['bezirk_id']
		]);
	}

	public function canOnlyAcceptStoreRequestsAsManager(ApiTester $I): void
	{
		// create a request
		$user2 = $I->createFoodsaver();
		$this->createStoreRequest($I, $user2['id']);

		// accept request
		$I->login($this->user[self::EMAIL]);
		$I->sendPatch(self::API_STORES . '/' . $this->store['id'] . '/requests/' . $user2['id']);
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		// user should not be active in store
		$I->seeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $user2['id'],
			'verantwortlich' => 0,
			'active' => 0
		]);
	}

	public function canNotAcceptNonexistingRequests(ApiTester $I): void
	{
		$user2 = $I->createFoodsaver();

		// accept request
		$I->login($this->manager[self::EMAIL]);
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
		$I->login($this->manager[self::EMAIL]);
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
		$I->login($this->user[self::EMAIL]);
		$I->sendDelete(self::API_STORES . '/' . $this->store['id'] . '/requests/' . $user2['id']);
		$I->seeResponseCodeIs(Http::FORBIDDEN);

		// user's request should still be there
		$I->seeInDatabase('fs_betrieb_team', [
			'betrieb_id' => $this->store['id'],
			'foodsaver_id' => $user2['id'],
			'verantwortlich' => 0,
			'active' => 0
		]);
	}

	public function canNotRejectNonexistingRequests(ApiTester $I): void
	{
		$user2 = $I->createFoodsaver();

		// reject request
		$I->login($this->manager[self::EMAIL]);
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
			'active' => 0
		]);
	}
}
