<?php

use Carbon\Carbon;

class StoreGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var Faker\Generator
	 */
	private $faker;

	/**
	 * @var \Foodsharing\Modules\Store\StoreGateway
	 */
	private $gateway;

	private $foodsaver;

	private $region_id = 241;

	private function storeData($store, $status = 'none'): array
	{
		$data = [
			'id' => $store['id'],
			'betrieb_status_id' => $store['betrieb_status_id'],
			'plz' => $store['plz'],
			'kette_id' => $store['kette_id'],
			'ansprechpartner' => $store['ansprechpartner'],
			'fax' => $store['fax'],
			'telefon' => $store['telefon'],
			'email' => $store['email'],
			'betrieb_kategorie_id' => $store['betrieb_kategorie_id'],
			'name' => $store['name'],
			'anschrift' => implode(' ', [$store['str'], $store['hsnr']]),
			'str' => $store['str'],
			'hsnr' => (string)$store['hsnr'],
			'bezirk_name' => 'Göttingen'
		];

		if ($status === 'team') {
			$data['verantwortlich'] = 0;
			$data['active'] = 1;
			unset($data['bezirk_name']);
		}

		return $data;
	}

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Store\StoreGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->faker = Faker\Factory::create('de_DE');
	}

	public function testGetPickupDates()
	{
		$store = $this->tester->createStore($this->region_id);
		$date = '2018-07-18';
		$time = '16:40:00';
		$datetime = $date . ' ' . $time;
		$dow = 3; /* above date is a wednesday */
		$fetcher = 2;
		$fsid = $this->foodsaver['id'];
		$this->tester->addRecurringPickup($store['id'], ['time' => $time, 'dow' => $dow, 'fetcher' => $fetcher]);
		$regularSlots = $this->gateway->getRegularPickups($store['id']);
		$this->assertEquals([
			[
				'dow' => 3,
				'time' => $time,
				'fetcher' => $fetcher
			]
		], $regularSlots);
		$this->gateway->addFetcher($fsid, $store['id'], new Carbon($datetime));
		$fetcherList = $this->gateway->listFetcher($store['id'], [$datetime]);

		$this->assertEquals([
			[
				'id' => $fsid,
				'name' => $this->foodsaver['name'],
				'photo' => null,
				'date' => $datetime,
				'confirmed' => 0
			]
		], $fetcherList);
	}

	public function testGetIrregularPickupDate()
	{
		$store = $this->tester->createStore($this->region_id);
		$expectedIsoDate = '2018-07-19T10:35:00Z';
		$fetcher = 1;
		$internalDate = Carbon::createFromFormat(DATE_ATOM, $expectedIsoDate);
		$date = $internalDate->copy()->setTimezone('Europe/Berlin')->format('Y-m-d H:i:s');
		$this->tester->addPickup($store['id'], ['time' => $date, 'fetchercount' => $fetcher]);
		$irregularSlots = $this->gateway->getOnetimePickups($store['id'], $internalDate);

		$this->assertEquals([
			[
			'date' => $internalDate->copy()->setTimezone('Europe/Berlin')->format('Y-m-d H:i:s'),
			'fetcher' => $fetcher
		]
		], $irregularSlots);
	}

	public function testIsInTeam()
	{
		$store = $this->tester->createStore($this->region_id);
		$this->assertEquals(\Foodsharing\Modules\Store\TeamStatus::NoMember,
			$this->gateway->getUserTeamStatus($this->foodsaver['id'], $store['id'])
		);

		$this->tester->addStoreTeam($store['id'], $this->foodsaver['id']);
		$this->assertEquals(\Foodsharing\Modules\Store\TeamStatus::Member,
			$this->gateway->getUserTeamStatus($this->foodsaver['id'], $store['id'])
		);

		$coordinator = $this->tester->createStoreCoordinator();
		$this->tester->addStoreTeam($store['id'], $coordinator['id'], true);
		$this->assertEquals(\Foodsharing\Modules\Store\TeamStatus::Coordinator,
			$this->gateway->getUserTeamStatus($coordinator['id'], $store['id'])
		);

		$waiter = $this->tester->createFoodsaver();
		$this->tester->addStoreTeam($store['id'], $waiter['id'], false, true);
		$this->assertEquals(\Foodsharing\Modules\Store\TeamStatus::WaitingList,
			$this->gateway->getUserTeamStatus($waiter['id'], $store['id'])
		);
	}

	public function testListStoresForFoodsaver()
	{
		$store = $this->tester->createStore($this->region_id);
		$this->assertEquals(
			$this->gateway->getMyStores($this->foodsaver['id'], $this->region_id),
			[
				'verantwortlich' => [],
				'team' => [],
				'waitspringer' => [],
				'anfrage' => [],
				'sonstige' => [$this->storeData($store)],
			]
		);

		$this->tester->addStoreTeam($store['id'], $this->foodsaver['id']);

		$this->assertEquals(
			$this->gateway->getMyStores($this->foodsaver['id'], $this->region_id),
			[
				'verantwortlich' => [],
				'team' => [$this->storeData($store, 'team')],
				'waitspringer' => [],
				'anfrage' => [],
				'sonstige' => [],
			]
		);
	}

	public function testUpdateExpiredBellsRemovesBellIfNoUnconfirmedFetchesAreInTheFuture()
	{
		$store = $this->tester->createStore($this->region_id);
		$foodsaver = $this->tester->createFoodsaver();

		$this->gateway->addFetcher($foodsaver['id'], $store['id'], new Carbon('1970-01-01'), );

		$this->tester->updateInDatabase(
			'fs_bell',
			['expiration' => '1970-01-01'],
			['identifier' => 'store-fetch-unconfirmed-' . $store['id']]
		); // outdate bell notification

		$this->gateway->updateExpiredBells();

		$this->tester->dontSeeInDatabase('fs_bell', ['identifier' => 'store-fetch-unconfirmed-' . $store['id']]);
	}
}
