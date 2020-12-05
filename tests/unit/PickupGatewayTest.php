<?php

use Carbon\Carbon;
use Faker\Factory;
use Faker\Generator;
use Foodsharing\Modules\Store\PickupGateway;

class PickupGatewayTest extends \Codeception\Test\Unit
{
	protected UnitTester $tester;
	private Generator $faker;
	private PickupGateway $gateway;

	private array $store;
	private array $foodsaver;
	private array $region;

	protected function _before()
	{
		$this->gateway = $this->tester->get(PickupGateway::class);
		$this->region = $this->tester->createRegion();
		$this->store = $this->tester->createStore($this->region['id']);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->faker = Factory::create('de_DE');
	}

	public function testGetPickupDates()
	{
		$date = '2018-07-18';
		$time = '16:40:00';
		$datetime = $date . ' ' . $time;
		$dow = 3; /* above date is a wednesday */
		$fetcher = 2;
		$fsid = $this->foodsaver['id'];

		$this->tester->addRecurringPickup($this->store['id'],
			['time' => $time, 'dow' => $dow, 'fetcher' => $fetcher]
		);
		$regularSlots = $this->gateway->getRegularPickups($this->store['id']);
		$this->assertEquals([
			[
				'dow' => 3,
				'time' => $time,
				'fetcher' => $fetcher,
			]
		], $regularSlots);

		$this->gateway->addFetcher($fsid, $this->store['id'], new Carbon($datetime));
		$fsList = $this->gateway->getPickupSignupsForDate($this->store['id'], new Carbon($datetime));

		$this->assertEquals([
			[
				'foodsaver_id' => $fsid,
				'date' => $datetime,
				'confirmed' => 0,
			]
		], $fsList);
	}

	public function testGetIrregularPickupDate()
	{
		$expectedIsoDate = '2018-07-19T10:35:00Z';
		$fetcher = 1;
		$internalDate = Carbon::createFromFormat(DATE_ATOM, $expectedIsoDate);
		$date = $internalDate->copy()->setTimezone('Europe/Berlin')->format('Y-m-d H:i:s');
		$this->tester->addPickup($this->store['id'], ['time' => $date, 'fetchercount' => $fetcher]);
		$irregularSlots = $this->gateway->getOnetimePickups($this->store['id'], $internalDate);

		$this->assertEquals([
			[
			'date' => $internalDate->copy()->setTimezone('Europe/Berlin')->format('Y-m-d H:i:s'),
			'fetcher' => $fetcher
		]
		], $irregularSlots);
	}

	public function testUpdateExpiredBellsRemovesBellIfNoUnconfirmedFetchesAreInTheFuture()
	{
		$foodsaver = $this->tester->createFoodsaver();

		$this->gateway->addFetcher($foodsaver['id'], $this->store['id'], new Carbon('1970-01-01'));

		$this->tester->updateInDatabase(
			'fs_bell',
			['expiration' => '1970-01-01'],
			['identifier' => 'store-fetch-unconfirmed-' . $this->store['id']]
		); // outdate bell notification

		$this->gateway->updateExpiredBells();

		$this->tester->dontSeeInDatabase('fs_bell', ['identifier' => 'store-fetch-unconfirmed-' . $this->store['id']]);
	}
}
