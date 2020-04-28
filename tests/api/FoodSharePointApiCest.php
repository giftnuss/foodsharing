<?php

namespace api;

use Codeception\Util\HttpCode as Http;
use Faker;

/**
 * Tests for the food share point api.
 */
class FoodSharePointApiCest
{
	private $user;
	private $faker;

	private const EMAIL = 'email';
	private const API_FSPS = 'api/foodSharePoints';
	private const ID = 'id';
	private const TEST_PICTURE = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAAAAAA6fptVAAAACklEQVR4nGNiAAAABgADNjd8qAAAAABJRU5ErkJggg==';

	public function _before(\ApiTester $I)
	{
		$this->user = $I->createFoodsaver();
		$this->faker = Faker\Factory::create('de_DE');
	}

	public function getFoodSharePoint(\ApiTester $I)
	{
		$fsp = $I->createFoodSharePoint($this->user[self::ID], 241);

		$I->login($this->user[self::EMAIL]);
		$I->sendGET(self::API_FSPS . '/' . $fsp[self::ID]);
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();
	}

	public function listNearbyFoodSharePoints(\ApiTester $I)
	{
		$I->createFoodSharePoint($this->user[self::ID], 241);

		$I->login($this->user[self::EMAIL]);
		$I->sendGET(self::API_FSPS . '/nearby?distance=30');
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		$I->sendGET(self::API_FSPS . '/nearby?lat=50&lon=9&distance=30');
		$I->seeResponseCodeIs(Http::OK);
		$I->seeResponseIsJson();

		$I->sendGET(self::API_FSPS . '/nearby?lat=50&lon=9&distance=51');
		$I->seeResponseCodeIs(Http::BAD_REQUEST);
		$I->seeResponseIsJson();
	}
}
