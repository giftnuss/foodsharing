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

	private $store;
	private $foodsaver;

	private $region_id = 241;

	private function storeData($status = 'none'): array
	{
		$data = [
			'id' => $this->store['id'],
			'betrieb_status_id' => $this->store['betrieb_status_id'],
			'plz' => $this->store['plz'],
			'kette_id' => $this->store['kette_id'],
			'ansprechpartner' => $this->store['ansprechpartner'],
			'fax' => $this->store['fax'],
			'telefon' => $this->store['telefon'],
			'email' => $this->store['email'],
			'betrieb_kategorie_id' => $this->store['betrieb_kategorie_id'],
			'name' => $this->store['name'],
			'anschrift' => implode(' ', [$this->store['str'], $this->store['hsnr']]),
			'str' => $this->store['str'],
			'hsnr' => (string)$this->store['hsnr'],
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
		$this->store = $this->tester->createStore($this->region_id);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->faker = Faker\Factory::create('de_DE');
	}

	public function testGetPickupDates()
	{
		$date = '2018-07-18';
		$time = '16:40:00';
		$datetime = $date . ' ' . $time;
		$dow = 3; /* above date is a wednesday */
		$fetcher = 2;
		$fsid = $this->foodsaver['id'];
		$this->tester->addRecurringPickup($this->store['id'], ['time' => $time, 'dow' => $dow, 'fetcher' => $fetcher]);
		$regularSlots = $this->gateway->getRegularPickups($this->store['id']);
		$this->assertEquals([
			[
				'dow' => 3,
				'time' => $time,
				'fetcher' => $fetcher
			]
		], $regularSlots);
		$this->gateway->addFetcher($fsid, $this->store['id'], new Carbon($datetime));
		$fetcherList = $this->gateway->listFetcher($this->store['id'], [$datetime]);

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

	public function testIsInTeam()
	{
		$this->assertEquals(\Foodsharing\Modules\Store\TeamStatus::NoMember,
			$this->gateway->getUserTeamStatus($this->foodsaver['id'], $this->store['id'])
		);

		$this->tester->addStoreTeam($this->store['id'], $this->foodsaver['id']);
		$this->assertEquals(\Foodsharing\Modules\Store\TeamStatus::Member,
			$this->gateway->getUserTeamStatus($this->foodsaver['id'], $this->store['id'])
		);

		$coordinator = $this->tester->createStoreCoordinator();
		$this->tester->addStoreTeam($this->store['id'], $coordinator['id'], true);
		$this->assertEquals(\Foodsharing\Modules\Store\TeamStatus::Coordinator,
			$this->gateway->getUserTeamStatus($coordinator['id'], $this->store['id'])
		);

		$waiter = $this->tester->createFoodsaver();
		$this->tester->addStoreTeam($this->store['id'], $waiter['id'], false, true);
		$this->assertEquals(\Foodsharing\Modules\Store\TeamStatus::WaitingList,
			$this->gateway->getUserTeamStatus($waiter['id'], $this->store['id'])
		);
	}

	public function testListStoresForFoodsaver()
	{
		$this->assertEquals(
			$this->gateway->getMyStores($this->foodsaver['id'], $this->region_id),
			[
				'verantwortlich' => [],
				'team' => [],
				'waitspringer' => [],
				'anfrage' => [],
				'sonstige' => [$this->storeData()],
			]
		);

		$this->tester->addStoreTeam($this->store['id'], $this->foodsaver['id']);

		$this->assertEquals(
			$this->gateway->getMyStores($this->foodsaver['id'], $this->region_id),
			[
				'verantwortlich' => [],
				'team' => [$this->storeData('team')],
				'waitspringer' => [],
				'anfrage' => [],
				'sonstige' => [],
			]
		);
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

	public function testGetNoTeamConversation()
	{
		$conversationId = $this->gateway->getBetriebConversation($this->store['id']);

		$this->tester->assertEquals(0, $conversationId);
	}

	public function testGetNoSpringerConversation()
	{
		$conversationId = $this->gateway->getBetriebConversation($this->store['id'], true);

		$this->tester->assertEquals(0, $conversationId);
	}
}
