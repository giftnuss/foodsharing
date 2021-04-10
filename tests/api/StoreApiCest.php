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
	private $foodsharer;
	private $user;
	private $teamMember;
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
		$this->foodsharer = $I->createFoodsharer();
		$this->user = $I->createFoodsaver();
		$this->teamMember = $I->createFoodsaver();
		$I->addStoreTeam($this->store[self::ID], $this->teamMember[self::ID], false);
		$this->manager = $I->createStoreCoordinator(null, ['bezirk_id' => $this->region['id']]);
		$I->addStoreTeam($this->store[self::ID], $this->manager[self::ID], true);
		$this->faker = Faker\Factory::create('de_DE');
	}

	public function getStore(ApiTester $I)
	{
		$I->login($this->teamMember[self::EMAIL]);
		$I->sendGET(self::API_STORES . '/' . $this->store[self::ID]);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['id' => $this->store[self::ID]]);
		$I->seeResponseContainsJson(['phone' => $this->store['telefon']]);
	}

	public function canOnlyAccessStoreAsFoodsaver(ApiTester $I)
	{
		$I->sendGET(self::API_STORES . '/' . $this->store[self::ID]);
		$I->seeResponseCodeIs(Http::UNAUTHORIZED);

		$I->login($this->foodsharer[self::EMAIL]);
		$I->sendGET(self::API_STORES . '/' . $this->store[self::ID]);
		$I->seeResponseCodeIs(Http::UNAUTHORIZED);
	}

	public function canOnlySeeStoreDetailsAsMember(ApiTester $I)
	{
		$I->login($this->user[self::EMAIL]);
		$I->sendGET(self::API_STORES . '/' . $this->store[self::ID]);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseContainsJson(['id' => $this->store[self::ID]]);
		$I->dontSeeResponseContainsJson(['phone' => $this->store['telefon']]);
	}

	public function canWriteStoreWallpostAndGetAllPosts(ApiTester $I): void
	{
		$I->login($this->teamMember[self::EMAIL]);
		$newWallPost = $this->faker->realText(200);
		$I->sendPOST(self::API_STORES . '/' . $this->store[self::ID] . '/posts', ['text' => $newWallPost]);

		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
		$I->seeInDatabase('fs_betrieb_notiz', [
			'foodsaver_id' => $this->teamMember[self::ID],
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
			'foodsaver_id' => $this->teamMember[self::ID],
			'text' => $this->faker->realText(100),
			'zeit' => $this->faker->dateTimeBetween('-14 days', '-30m')->format('Y-m-d H:i:s'),
			'milestone' => Milestone::NONE,
		];
		$postId = $I->haveInDatabase('fs_betrieb_notiz', $wallPost);

		$I->login($this->teamMember[self::EMAIL]);

		$I->sendDELETE(self::API_STORES . '/' . $this->store[self::ID] . '/posts/' . $postId);

		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
		$I->dontSeeInDatabase('fs_betrieb_notiz', ['id' => $postId]);

		$I->seeInDatabase('fs_store_log', [
			'store_id' => $this->store[self::ID],
			'fs_id_a' => $this->teamMember[self::ID],
			'fs_id_p' => $this->teamMember[self::ID],
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
			'foodsaver_id' => $this->teamMember[self::ID],
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
			'fs_id_p' => $this->teamMember[self::ID],
			'content' => $wallPost['text'],
			'date_reference' => $wallPost['zeit'],
			'action' => StoreLogAction::DELETED_FROM_WALL,
		]);
	}

	public function storeManagerCannotRemoveNewStorePost(ApiTester $I): void
	{
		$wallPost = [
			'betrieb_id' => $this->store[self::ID],
			'foodsaver_id' => $this->teamMember[self::ID],
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
}
