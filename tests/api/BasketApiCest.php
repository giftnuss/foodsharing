<?php

namespace api;

/**
 * Tests for the basket api.
 */
class BasketApiCest
{
	private $user;

	private const EMAIL = 'email';
	private const API_BASKETS = 'api/baskets';
	private const ID = 'id';
	private const TEST_PICTURE = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAAAAAA6fptVAAAACklEQVR4nGNiAAAABgADNjd8qAAAAABJRU5ErkJggg==';

	public function _before(\ApiTester $I)
	{
		$this->user = $I->createFoodsaver();
	}

	public function getBasket(\ApiTester $I)
	{
		$basket = $I->createFoodbasket($this->user[self::ID]);

		$I->login($this->user[self::EMAIL]);
		$I->sendGET(self::API_BASKETS . '/' . $basket[self::ID]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function removeExistingBasket(\ApiTester $I)
	{
		$basket = $I->createFoodbasket($this->user[self::ID]);

		$I->login($this->user[self::EMAIL]);
		$I->sendDELETE(self::API_BASKETS . '/' . $basket[self::ID]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->sendGET(self::API_BASKETS . '/' . $basket[self::ID]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
	}

	public function removeNonExistingBasket(\ApiTester $I)
	{
		$I->login($this->user[self::EMAIL]);
		$I->sendDELETE(self::API_BASKETS . '/999999');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
	}

	public function listMyBaskets(\ApiTester $I)
	{
		$I->createFoodbasket($this->user[self::ID]);

		$I->login($this->user[self::EMAIL]);
		$I->sendGET(self::API_BASKETS . '?type=mine');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function listBasketCoordinates(\ApiTester $I)
	{
		$I->createFoodbasket($this->user[self::ID]);

		$I->login($this->user[self::EMAIL]);
		$I->sendGET(self::API_BASKETS . '?type=coordinates');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function addBasket(\ApiTester $I)
	{
		$I->login($this->user[self::EMAIL]);
		$I->sendPOST(self::API_BASKETS, ['description' => 'test description']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function noUnauthorizedActions(\ApiTester $I)
	{
		$basket = $I->createFoodbasket($this->user[self::ID]);

		$I->sendGET(self::API_BASKETS . '?type=mine');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
		$I->sendGET(self::API_BASKETS . '?type=coordinates');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
		$I->sendGET(self::API_BASKETS . '/' . $basket[self::ID]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
		$I->sendDELETE(self::API_BASKETS . '/' . $basket[self::ID]);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
		$I->sendPOST(self::API_BASKETS);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
	}

	public function setEmptyPicture(\ApiTester $I)
	{
		$basket = $I->createFoodbasket($this->user[self::ID]);

		$I->login($this->user[self::EMAIL]);
		$I->sendPUT(self::API_BASKETS . '/' . $basket[self::ID] . '/picture');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
	}

	public function setValidPicture(\ApiTester $I)
	{
		$basket = $I->createFoodbasket($this->user[self::ID]);

		$I->login($this->user[self::EMAIL]);
		$I->haveHttpHeader('Content-Type', 'image/png');
		$I->sendPUT(self::API_BASKETS . '/' . $basket[self::ID] . '/picture', base64_decode(self::TEST_PICTURE));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
	}

	public function setInvalidPicture(\ApiTester $I)
	{
		$basket = $I->createFoodbasket($this->user[self::ID]);

		$I->login($this->user[self::EMAIL]);
		$I->haveHttpHeader('Content-Type', 'image/png');
		$I->sendPUT(self::API_BASKETS . '/' . $basket[self::ID] . '/picture', substr(base64_decode(self::TEST_PICTURE), 0, 10));
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::BAD_REQUEST);
	}

	public function removePicture(\ApiTester $I)
	{
		$basket = $I->createFoodbasket($this->user[self::ID]);

		$I->login($this->user[self::EMAIL]);
		$I->sendDELETE(self::API_BASKETS . '/' . $basket[self::ID] . '/picture');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
	}
}
