<?php

namespace Foodsharing\unit;

use Foodsharing\Services\StoreService;

class StoreServiceTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;
	/**
	 * @var StoreService
	 */
	private $service;

	private $region_id;
	private $foodsaver;

	public function _before()
	{
		$this->service = $this->tester->get(StoreService::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->region_id = $this->tester->createRegion()['id'];
	}

	public function testPickupSlotAvailableRegular()
	{
		$store = $this->tester->createStore($this->region_id);
		$foodsaver2 = $this->tester->createFoodsaver();
		$date = '2018-07-18';
		$time = '16:40:00';
		$datetime = \DateTime::createFromFormat(DATE_ATOM, $date . 'T' . $time . 'Z');
		$dow = 3;
		$fetcher = 2;
		$this->tester->addRecurringPickup($store['id'], ['time' => $time, 'dow' => $dow, 'fetcher' => $fetcher]);
		$this->assertTrue($this->service->pickupSlotAvailable($store['id'], $datetime));
		$this->tester->addCollector($this->foodsaver['id'], $store['id'], ['date' => $datetime]);
		$this->assertTrue($this->service->pickupSlotAvailable($store['id'], $datetime));
		$this->tester->addCollector($foodsaver2['id'], $store['id'], ['date' => $datetime]);
		$this->assertFalse($this->service->pickupSlotAvailable($store['id'], $datetime));
	}

	public function testPickupSlotAvailableMixed()
	{
		$store = $this->tester->createStore($this->region_id);
		$foodsaver2 = $this->tester->createFoodsaver();
		$foodsaver3 = $this->tester->createFoodsaver();
		$foodsaver4 = $this->tester->createFoodsaver();
		$date = '2018-07-18';
		$time = '16:40:00';
		$datetime = \DateTime::createFromFormat(DATE_ATOM, $date . 'T' . $time . 'Z');
		$dow = 3;
		$fetcher = 2;
		$this->tester->addRecurringPickup($store['id'], ['time' => $time, 'dow' => $dow, 'fetcher' => $fetcher]);
		$this->tester->addPickup($store['id'], ['time' => $datetime, 'fetchercount' => $fetcher]);
		$this->assertTrue($this->service->pickupSlotAvailable($store['id'], $datetime));
		$this->tester->addCollector($this->foodsaver['id'], $store['id'], ['date' => $datetime]);
		$this->assertTrue($this->service->pickupSlotAvailable($store['id'], $datetime));
		$this->tester->addCollector($foodsaver2['id'], $store['id'], ['date' => $datetime]);
		$this->assertTrue($this->service->pickupSlotAvailable($store['id'], $datetime));
		$this->tester->addCollector($foodsaver3['id'], $store['id'], ['date' => $datetime]);
		$this->assertTrue($this->service->pickupSlotAvailable($store['id'], $datetime));
		$this->tester->addCollector($foodsaver4['id'], $store['id'], ['date' => $datetime]);
		$this->assertFalse($this->service->pickupSlotAvailable($store['id'], $datetime));
	}

	public function testPickupSlotNotAvailableEmpty()
	{
		$store = $this->tester->createStore($this->region_id);
		$date = '2018-09-18';
		$time = '16:50:00';
		$datetime = \DateTime::createFromFormat(DATE_ATOM, $date . 'T' . $time . 'Z');
		$this->assertFalse($this->service->pickupSlotAvailable($store['id'], $datetime));
	}
}
