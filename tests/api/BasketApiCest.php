<?php

namespace api;

/**
 * Tests for the basket api.
 */
class BasketApiCest
{
	private $user;

	private const EMAIL = 'email';
	private const API_BASKETS_BASKET = 'api/baskets/basket/';
	private const ID = 'id';

	public function _before(\ApiTester $I)
	{
		$this->user = $I->createFoodsaver();
	}

	public function getBasket(\ApiTester $I)
	{
		$basket = $I->createFoodbasket($this->user[self::ID]);

		$I->login($this->user[self::EMAIL]);
		$I->sendGET(self::API_BASKETS_BASKET . $basket[self::ID]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function removeExistingBasket(\ApiTester $I)
	{
		$basket = $I->createFoodbasket($this->user[self::ID]);

		$I->login($this->user[self::EMAIL]);
		$I->sendDELETE('api/baskets/remove/' . $basket[self::ID]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->sendGET(self::API_BASKETS_BASKET . $basket[self::ID]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
	}

	public function removeNonExistingBasket(\ApiTester $I)
	{
		$I->login($this->user[self::EMAIL]);
		$I->sendDELETE('api/baskets/remove/999999');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
	}

	public function listMyBaskets(\ApiTester $I)
	{
		$I->createFoodbasket($this->user[self::ID]);

		$I->login($this->user[self::EMAIL]);
		$I->sendGET('api/baskets/mybaskets');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function listBasketCoordinates(\ApiTester $I)
	{
		$I->createFoodbasket($this->user[self::ID]);

		$I->login($this->user[self::EMAIL]);
		$I->sendGET('api/baskets/coordinates');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function addBasket(\ApiTester $I)
	{
		$I->login($this->user[self::EMAIL]);
		$I->sendPOST('api/baskets/add', ['description' => 'test description']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function noUnauthorizedActions(\ApiTester $I)
	{
		$basket = $I->createFoodbasket($this->user[self::ID]);

		$I->sendGET('api/baskets/coordinates');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
		$I->sendGET('api/baskets/mybaskets');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
		$I->sendGET(self::API_BASKETS_BASKET . $basket[self::ID]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
		$I->sendDELETE('api/baskets/remove/' . $basket[self::ID]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
		$I->sendPOST('api/baskets/add');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
	}
}
