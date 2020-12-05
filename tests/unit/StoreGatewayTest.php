<?php

use Faker\Factory;
use Faker\Generator;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Store\TeamStatus;

class StoreGatewayTest extends \Codeception\Test\Unit
{
	protected UnitTester $tester;
	private Generator $faker;
	private StoreGateway $gateway;

	private $store;
	private $foodsaver;
	private $region;

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
			'bezirk_name' => $this->region['name']
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
		$this->gateway = $this->tester->get(StoreGateway::class);
		$this->region = $this->tester->createRegion();
		$this->store = $this->tester->createStore($this->region['id']);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->faker = Factory::create('de_DE');
	}

	public function testIsInTeam()
	{
		$this->assertEquals(TeamStatus::NoMember,
			$this->gateway->getUserTeamStatus($this->foodsaver['id'], $this->store['id'])
		);

		$this->tester->addStoreTeam($this->store['id'], $this->foodsaver['id']);
		$this->assertEquals(TeamStatus::Member,
			$this->gateway->getUserTeamStatus($this->foodsaver['id'], $this->store['id'])
		);

		$coordinator = $this->tester->createStoreCoordinator();
		$this->tester->addStoreTeam($this->store['id'], $coordinator['id'], true);
		$this->assertEquals(TeamStatus::Coordinator,
			$this->gateway->getUserTeamStatus($coordinator['id'], $this->store['id'])
		);

		$waiter = $this->tester->createFoodsaver();
		$this->tester->addStoreTeam($this->store['id'], $waiter['id'], false, true);
		$this->assertEquals(TeamStatus::WaitingList,
			$this->gateway->getUserTeamStatus($waiter['id'], $this->store['id'])
		);
	}

	public function testListStoresForFoodsaver()
	{
		$this->assertEquals(
			[
				'verantwortlich' => [],
				'team' => [],
				'waitspringer' => [],
				'requested' => [],
				'sonstige' => [$this->storeData()],
			],
			$this->gateway->getMyStores($this->foodsaver['id'], $this->region['id'])
		);

		$this->tester->addStoreTeam($this->store['id'], $this->foodsaver['id']);

		$this->assertEquals(
			[
				'verantwortlich' => [],
				'team' => [$this->storeData('team')],
				'waitspringer' => [],
				'requested' => [],
				'sonstige' => [],
			],
			$this->gateway->getMyStores($this->foodsaver['id'], $this->region['id'])
		);
	}

	public function testUpdateStoreRegion()
	{
		$newRegion = $this->tester->createRegion();

		$updates = $this->gateway->updateStoreRegion($this->store['id'], $newRegion['id']);

		$this->tester->seeInDatabase('fs_betrieb', ['bezirk_id' => $newRegion['id'], 'id' => $this->store['id']]);
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
