<?php

namespace Foodsharing\api;

class PickupApiCest
{
	private $user;
	private $store;
	private $region;

	public function _before(\ApiTester $I)
	{
		$this->user = $I->createFoodsaver();
		$this->region = $I->createRegion();
		$this->store = $I->createStore($this->region['id']);
		$I->addStoreTeam($this->store['id'], $this->user['id']);
	}

	public function acceptsDifferentIsoFormats(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$I->addPickup($this->store['id'], ['time' => '2019-02-13 13:45:30', 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/2019-02-13T13:45:30+0000/signup');
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->addPickup($this->store['id'], ['time' => '2019-02-13 13:46:30', 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/2019-02-13T13:46:30+01:00/signup');
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->addPickup($this->store['id'], ['time' => '2019-02-13 13:47:30', 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/2019-02-13T13:47:30-01:00/signup');
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$I->addPickup($this->store['id'], ['time' => '2019-02-13 13:48:30', 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/2019-02-13T13:48:30Z/signup');
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}
}
