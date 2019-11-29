<?php

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;
use Foodsharing\Modules\Store\StoreGateway;
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

	/**
	 * @var StoreGateway
	 */
	private $gateway;

	/**
	 * @var Faker\Generator
	 */
	private $faker;

	private $region_id;
	private $foodsaver;

	public function _before()
	{
		$this->service = $this->tester->get(StoreService::class);
		$this->gateway = $this->tester->get(StoreGateway::class);
		$this->faker = $this->faker = Faker\Factory::create('de_DE');
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->region_id = $this->tester->createRegion()['id'];
	}

	public function testPickupSlotAvailableRegular()
	{
		$store = $this->tester->createStore($this->region_id);
		$foodsaver2 = $this->tester->createFoodsaver();
		$date = Carbon::now()->add('2 days')->hours(16)->minutes(30)->seconds(0)->microseconds(0);
		$dow = $date->weekday();
		$fetcher = 2;
		$this->tester->addRecurringPickup($store['id'], ['time' => '16:30:00', 'dow' => $dow, 'fetcher' => $fetcher]);
		$this->assertTrue($this->service->pickupSlotAvailable($store['id'], $date, $this->foodsaver['id']));
		$this->tester->addCollector($this->foodsaver['id'], $store['id'], ['date' => $date]);
		$this->assertTrue($this->service->pickupSlotAvailable($store['id'], $date, $foodsaver2['id']));
		$this->tester->addCollector($foodsaver2['id'], $store['id'], ['date' => $date]);
		$this->assertFalse($this->service->pickupSlotAvailable($store['id'], $date));
	}

	public function testPickupSlotAvailableMixed()
	{
		$store = $this->tester->createStore($this->region_id);
		$date = Carbon::now()->add('3 days')->hours(16)->minutes(40)->seconds(0)->microseconds(0);
		$dow = $date->format('w');
		$this->tester->addRecurringPickup($store['id'], ['time' => '16:40:00', 'dow' => $dow, 'fetcher' => 2]);
		$this->tester->addPickup($store['id'], ['time' => $date, 'fetchercount' => 1]);
		$this->assertTrue($this->service->pickupSlotAvailable($store['id'], $date));
		$this->tester->addCollector($this->foodsaver['id'], $store['id'], ['date' => $date]);
		$this->assertFalse($this->service->pickupSlotAvailable($store['id'], $date));
	}

	public function testSinglePickupTimeProperlyTakenIntoAccount()
	{
		$store = $this->tester->createStore($this->region_id);
		$user = $this->tester->createFoodsaver();
		$date = Carbon::instance($this->faker->dateTimeInInterval('+2 days', '+10 days'));
		$this->tester->addPickup($store['id'], ['time' => $date, 'fetchercount' => 1]);
		$date2 = $date->copy()->addHours(1);
		$this->tester->addPickup($store['id'], ['time' => $date2, 'fetchercount' => 1]);
		$this->assertFalse($this->service->joinPickup($store['id'], $date, $this->foodsaver['id']));
		$this->expectException(\DomainException::class);
		$this->service->joinPickup($store['id'], $date, $user['id']);
	}

	public function testPickupSlotNotAvailableEmpty()
	{
		$store = $this->tester->createStore($this->region_id);
		$date = Carbon::now()->add('1 day')->microseconds(0);

		$this->assertFalse($this->service->pickupSlotAvailable($store['id'], $date));
	}

	public function testUserCanOnlySignupOncePerSlot()
	{
		$store = $this->tester->createStore($this->region_id);
		$date = Carbon::now()->add('4 days')->hours(16)->minutes(20)->seconds(0)->microseconds(0);
		$dow = $date->format('w');
		$fetcher = 2;
		$this->tester->addRecurringPickup($store['id'], ['time' => '16:20:00', 'dow' => $dow, 'fetcher' => $fetcher]);
		$this->assertFalse($this->service->joinPickup($store['id'], $date, $this->foodsaver['id']));
		$this->expectException(DomainException::class);
		$this->service->joinPickup($store['id'], $date, $this->foodsaver['id']);
	}

	public function testUserCanOnlySignupForFuturePickups()
	{
		$store = $this->tester->createStore($this->region_id);
		$pickup = new Carbon('1 hour ago');
		$this->tester->addPickup($store['id'], ['time' => $pickup, 'fetchercount' => 1]);
		$this->expectException(DomainException::class);
		$this->service->joinPickup($store['id'], $pickup, $this->foodsaver['id']);
	}

	public function testUserCanOnlySignupForNotTooMuchInTheFuturePickups()
	{
		$interval = CarbonInterval::weeks(3);
		$store = $this->tester->createStore($this->region_id, null, null, ['prefetchtime' => $interval->totalSeconds - 360]);
		/* that pickup is now at least some minutes too much in the future to sign up */
		$pickup = Carbon::tomorrow()->add($interval)->microseconds(0);
		/* use recurring pickup here because signing up for single pickups should work indefinitely */
		$this->tester->addRecurringPickup($store['id'], ['time' => $pickup->toTimeString(), 'dow' => $pickup->weekday(), 'fetcher' => 1]);
		$this->expectException(DomainException::class);
		$this->service->joinPickup($store['id'], $pickup, $this->foodsaver['id']);
		$this->assertFalse($this->service->joinPickup($store['id'], $pickup->sub('1 week'), $this->foodsaver['id']));
	}

	public function testUserCanSignupForManualFarInTheFuturePickups()
	{
		$interval = CarbonInterval::weeks(3);
		$store = $this->tester->createStore($this->region_id, null, null, ['prefetchtime' => $interval->totalSeconds - 360]);
		/* that pickup is now at least some minutes too much in the future to sign up */
		$pickup = Carbon::now()->add($interval)->microseconds(0);
		/* use single pickup, which should work indefinitely */
		$this->tester->addPickup($store['id'], ['time' => $pickup, 'fetchercount' => 1]);
		$this->assertFalse($this->service->joinPickup($store['id'], $pickup, $this->foodsaver['id']));
	}

	public function testUpdateExpiredBellsUpdatesBellCountIfStillUnconfirmedFetchesAreInTheFuture()
	{
		$store = $this->tester->createStore($this->region_id);
		$foodsaver = $this->tester->createFoodsaver();

		$this->tester->addPickup($store['id'], ['time' => '2150-01-01 00:00:00', 'fetchercount' => 1]);
		$this->tester->addPickup($store['id'], ['time' => '2150-01-02 00:00:00', 'fetchercount' => 1]);

		$this->assertFalse($this->service->joinPickup($store['id'], new Carbon('2150-01-01 00:00:00'), $foodsaver['id']));
		$this->assertFalse($this->service->joinPickup($store['id'], new Carbon('2150-01-02 00:00:00'), $foodsaver['id']));

		// As we can't change the NOW() time in the database for the test, we have to move one fetch date to the past:
		$this->tester->updateInDatabase(
			'fs_abholer',
			['date' => '1970-01-01 00:00:00'],
			['date' => '2150-01-01 00:00:00']
		);

		/* Now, we have two unconfirmed fetch dates in the database: One that is in the future (2150-01-02) and one
		 * that is in the past (1970-01-01).
		 */

		$this->tester->updateInDatabase(
			'fs_bell',
			['expiration' => '1970-01-01 00:00:00'],
			['identifier' => 'store-fetch-unconfirmed-' . $store['id']]
		); // outdate bell notification

		$this->gateway->updateExpiredBells();

		$this->tester->seeInDatabase('fs_bell', ['vars like' => '%"count";i:1;%']); // The bell should have a count of 1 now - vars are serialized, that's why it looks so strange
	}

	/**
	 * If there are muliple fetches to confirm for one BIEB, only one store bell should be generated. It should
	 * have the date of the soonest fetch as its date, and it should contain the number of only the unconfirmed fetch
	 * dates that are in the future.
	 */
	public function testStoreBellsAreGeneratedCorrectly()
	{
		$this->tester->clearTable('fs_abholer');

		$user = $this->tester->createFoodsaver();
		$store = $this->tester->createStore(0);

		$pastDate = Carbon::instance($this->faker->dateTimeBetween($max = 'now'));
		$soonDate = Carbon::instance($this->faker->dateTimeBetween('+1 days', '+2 days'));
		$futureDate = Carbon::instance($this->faker->dateTimeBetween('+7 days', '+14 days'));

		$this->tester->addPickup($store['id'], ['time' => $soonDate, 'fetchercount' => 2]);
		$this->tester->addPickup($store['id'], ['time' => $futureDate, 'fetchercount' => 2]);

		$this->gateway->addFetcher($user['id'], $store['id'], $pastDate);
		$this->service->joinPickup($store['id'], $soonDate, $user['id']);
		$this->service->joinPickup($store['id'], $futureDate, $user['id']);

		$this->tester->seeNumRecords(3, 'fs_abholer');

		$this->tester->seeNumRecords(1, 'fs_bell', ['identifier' => 'store-fetch-unconfirmed-' . $store['id']]);

		$bellVars = $this->tester->grabFromDatabase('fs_bell', 'vars', ['identifier' => 'store-fetch-unconfirmed-' . $store['id']]);
		$vars = unserialize($bellVars);
		$this->assertEquals(2, $vars['count']);

		$bellDate = $this->tester->grabFromDatabase('fs_bell', 'time', ['identifier' => 'store-fetch-unconfirmed-' . $store['id']]);
		$this->assertEquals($soonDate->format('Y-m-d H:i:s'), $bellDate);
	}

	public function testNextAvailablePickupTime()
	{
		$date = Carbon::now()->add('2 days')->hours(16)->minutes(30)->seconds(0)->microseconds(0);
		$maxDate = $date->add('1 day');
		$dow = $date->weekday();

		// stores should result is a non-null date if there are free slots available
		$store = $this->tester->createStore($this->region_id, null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED]);
		$this->tester->addRecurringPickup($store['id'], ['time' => '16:30:00', 'dow' => $dow, 'fetcher' => 1]);
		$this->assertEquals($this->service->getNextAvailablePickupTime($store['id'], $maxDate), $date);

		$this->tester->addCollector($this->foodsaver['id'], $store['id'], ['date' => $date]);
		$this->assertNull($this->service->getNextAvailablePickupTime($store['id'], $maxDate));
	}

	public function testAvailablePickupStatus()
	{
		$date = Carbon::now()->add('2 days')->hours(16)->minutes(30)->seconds(0)->microseconds(0);
		$dow = $date->weekday();

		// stores should have status != 0 if free slots are available
		$store = $this->tester->createStore($this->region_id, null, null, ['betrieb_status_id' => CooperationStatus::COOPERATION_ESTABLISHED]);
		$this->tester->addRecurringPickup($store['id'], ['time' => '16:30:00', 'dow' => $dow, 'fetcher' => 1]);
		$this->assertEquals($this->service->getAvailablePickupStatus($store['id']), 2);

		$this->tester->addCollector($this->foodsaver['id'], $store['id'], ['date' => $date]);
		$this->assertEquals($this->service->getAvailablePickupStatus($store['id']), 0);
	}
}
