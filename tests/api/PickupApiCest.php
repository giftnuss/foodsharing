<?php

namespace Foodsharing\api;

use Carbon\Carbon;

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
		$id = $this->user['id'];
		$pickupBaseDate = Carbon::now()->add('2 days');
		$pickupBaseDate->hours(13)->minutes(45)->seconds(0);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s') . '+0000/' . $id);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$pickupBaseDate->minutes(50);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->copy()->setTimezone('+01:00')->format('Y-m-d\TH:i:s') . '.000+01:00/' . $id);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$pickupBaseDate->minutes(55);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->copy()->setTimezone('-01:00')->format('Y-m-d\TH:i:s') . '-01:00/' . $id);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
		$pickupBaseDate->minutes(35);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s') . 'Z/' . $id);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->seeResponseIsJson();
	}

	public function signupAsWaiterDoesNotWork(\ApiTester $I)
	{
		$waiter = $I->createFoodsaver();
		$I->addStoreTeam($this->store['id'], $waiter['id'], false, true);
		$I->login($waiter['email']);
		$pickupBaseDate = Carbon::now()->add('2 days');
		$pickupBaseDate->hours(14)->minutes(50)->seconds(0);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->toIso8601String() . '/' . $this->user['id']);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::FORBIDDEN);
	}

	public function signupReturnsPickupConfirmationState(\ApiTester $I)
	{
		$I->login($this->user['email']);
		$pickupBaseDate = Carbon::now()->add('2 days');
		$pickupBaseDate->hours(14)->minutes(45)->seconds(0);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->toIso8601String() . '/' . $this->user['id']);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->canSeeResponseContainsJson([
			'isConfirmed' => false
		]);
	}

	public function signupAsCoordinarIsPreconfirmed(\ApiTester $I)
	{
		$pickupBaseDate = Carbon::now()->add('2 days');
		$pickupBaseDate->hours(16)->minutes(45)->seconds(0);
		$coordinator = $I->createStoreCoordinator();
		$I->addStoreTeam($this->store['id'], $coordinator['id'], true, false, true);
		$I->login($coordinator['email']);
		$I->addPickup($this->store['id'], ['time' => $pickupBaseDate, 'fetchercount' => 2]);
		$I->sendPOST('api/stores/' . $this->store['id'] . '/pickups/' . $pickupBaseDate->toIso8601String() . '/' . $coordinator['id']);
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
		$I->canSeeResponseContainsJson([
			'isConfirmed' => true
		]);
	}
}
