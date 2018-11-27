<?php

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
		$time1 = '2018-07-18 16:40';
		$fsid = $this->foodsaver['id'];
		$this->tester->addRecurringPickup($store['id'], ['time' => $time1]);
		$this->gateway->addFetcher($fsid, $store['id'], $time1);
		$fetcherList = $this->gateway->listFetcher($store['id'], [$time1]);

		$this->assertEquals($fetcherList, [
			[
				'id' => $fsid,
				'name' => $this->foodsaver['name'],
				'photo' => null,
				'date' => $time1 . ':00',
				'confirmed' => 0
			]
		]);
	}

	public function testIsInTeam()
	{
		$store = $this->tester->createStore($this->region_id);
		$this->assertFalse(
			$this->gateway->isInTeam($this->foodsaver['id'], $store['id'])
		);

		$this->tester->addStoreTeam($store['id'], $this->foodsaver['id']);
		$this->assertTrue(
			$this->gateway->isInTeam($this->foodsaver['id'], $store['id'])
		);
	}

	public function testListStoresForFoodsaver()
	{
		$store = $this->tester->createStore($this->region_id);
		$this->assertEquals(
			$this->gateway->getMyBetriebe($this->foodsaver['id'], $this->region_id),
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
			$this->gateway->getMyBetriebe($this->foodsaver['id'], $this->region_id),
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

		$this->gateway->addFetcher($foodsaver['id'], $store['id'], '1970-01-01');

		$this->tester->updateInDatabase(
			'fs_bell',
			['expiration' => '1970-01-01'],
			['identifier' => 'store-' . $store['id']]
		); // outdate bell notification

		$this->gateway->updateExpiredBells();

		$this->tester->dontSeeInDatabase('fs_bell', ['identifier' => 'store-' . $store['id']]);
	}

	public function testUpdateExpiredBellsUpdatesBellCountIfStillUnconfirmedFetchesAreInTheFuture()
	{
		$store = $this->tester->createStore($this->region_id);
		$foodsaver = $this->tester->createFoodsaver();

		$this->gateway->addFetcher($foodsaver['id'], $store['id'], '2150-01-01');
		$this->gateway->addFetcher($foodsaver['id'], $store['id'], '2150-01-02');

		// As we can't cange the NOW() time in the database for the test, we have to move one fetch date to the past:
		$this->tester->updateInDatabase(
			'fs_abholer',
			['date' => '1970-01-01'],
			['date' => '2150-01-01']
		);

		/* Now, we have two unconfirmed fetch dates in the database: One that is in the future (2150-01-02) and one
		 * that is in the past (1970-01-01).
		 */

		$this->tester->updateInDatabase(
			'fs_bell',
			['expiration' => '1970-01-01'],
			['identifier' => 'store-' . $store['id']]
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

		$pastDate = $this->faker->dateTimeBetween($max = 'now')->format('Y-m-d H:i:s');
		$soonDate = $this->faker->dateTimeBetween('+1 days', '+2 days')->format('Y-m-d H:i:s');
		$futureDate = $this->faker->dateTimeBetween('+7 days', '+14 days')->format('Y-m-d H:i:s');

		$this->gateway->addFetcher($user['id'], $store['id'], $pastDate);
		$this->gateway->addFetcher($user['id'], $store['id'], $soonDate);
		$this->gateway->addFetcher($user['id'], $store['id'], $futureDate);

		$this->tester->seeNumRecords(3, 'fs_abholer');

		$this->tester->seeNumRecords(1, 'fs_bell', ['identifier' => 'store-' . $store['id']]);

		$bellVars = $this->tester->grabFromDatabase('fs_bell', 'vars', ['identifier' => 'store-' . $store['id']]);
		$vars = unserialize($bellVars);
		$this->assertEquals(2, $vars['count']);

		$bellDate = $this->tester->grabFromDatabase('fs_bell', 'time', ['identifier' => 'store-' . $store['id']]);
		$this->assertEquals($soonDate, $bellDate);
	}
}