<?php

namespace api;

/**
 * Tests for the basket api.
 */
class BasketApiCest
{
	public function _before(\ApiTester $I)
	{
		$this->tester = $I;
		$this->user = $I->createFoodsaver();
		$this->region = $I->createRegion();
	}

	public function getBasket(\ApiTester $I)
	{
		$basket = $I->createFoodbasket($this->user['id']);
		
		$I->login($this->user['email']);
		$I->sendGET('api/baskets/basket/' . $basket['id']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}
	
	public function removeExistingBasket(\ApiTester $I)
	{
		$basket = $I->createFoodbasket($this->user['id']);
		
		$I->login($this->user['email']);
		$I->sendDELETE('api/baskets/remove/' . $basket['id']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->sendGET('api/baskets/basket/' . $basket['id']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
	}
	
	public function removeNonExistingBasket(\ApiTester $I) {
		$I->login($this->user['email']);
		$I->sendDELETE('api/baskets/remove/999999');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NOT_FOUND);
	}

	public function listMyBaskets(\ApiTester $I)
	{
		$I->createFoodbasket($this->user['id']);
		
		$I->login($this->user['email']);
		$I->sendGET('api/baskets/mybaskets');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function listBasketCoordinates(\ApiTester $I)
	{
		$I->createFoodbasket($this->user['id']);
		
		$I->login($this->user['email']);
		$I->sendGET('api/baskets/coordinates');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}
	
	public function addBasket(\ApiTester $I) {
		$I->login($this->user['email']);
		$I->sendPOST('api/baskets/add', ['description' => 'test description']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function noUnauthorizedActions(\ApiTester $I)
	{
		$basket = $I->createFoodbasket($this->user['id']);
		
		$I->sendGET('api/baskets/coordinates');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
		$I->sendGET('api/baskets/mybaskets');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
		$I->sendGET('api/baskets/basket/' . $basket['id']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
		$I->sendDELETE('api/baskets/remove/' . $basket['id']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
		$I->sendPOST('api/baskets/add');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::UNAUTHORIZED);
	}
}
