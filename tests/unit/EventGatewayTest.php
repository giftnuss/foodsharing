<?php

class EventGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Event\EventGateway
	 */
	private $gateway;

	/**
	 * @var Faker\Generator
	 */
	private $faker;

	protected $foodsaver;
	protected $regionGateway;
	protected $region;
	protected $childRegion;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Event\EventGateway::class);
		$this->faker = Faker\Factory::create('de_DE');

		$this->regionGateway = $this->tester->get(\Foodsharing\Modules\Region\RegionGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->region = $this->tester->createRegion('God');
		$this->tester->addBezirkMember($this->region['id'], $this->foodsaver['id']);
		$this->childRegion = $this->tester->createRegion('Jesus', $this->region['id']);
	}

	public function testAddLocation()
	{
		$name = $this->faker->company;
		$lat = $this->faker->latitude;
		$lon = $this->faker->longitude;
		$address = $this->faker->streetAddress;
		$zip = $this->faker->postcode;
		$city = $this->faker->city;
		$id = $this->gateway->addLocation($name, $lat, $lon, $address, $zip, $city);
		$this->assertGreaterThan(0, $id);
		$this->tester->seeInDatabase('fs_location', ['id' => $id, 'name' => $name, 'lat' => $lat, 'lon' => $lon, 'street' => $address, 'zip' => $zip, 'city' => $city]);
	}

	public function testAddEvent()
	{
		$event = [
			'bezirk_id' => $this->region['id'],
			'location_id' => null,
			'public' => 0,
			'name' => 'name',
			'start' => '2018-09-01 12:00',
			'end' => '2018-09-30 12:00',
			'description' => 'd',
			'bot' => 0,
			'online' => 0,
			'otherStuff' => 'that should not bother...'
		];
		$id = $this->gateway->addEvent($this->foodsaver['id'], $event);
		$this->assertGreaterThan(0, $id);
		unset($event['otherStuff']);
		$event['foodsaver_id'] = $this->foodsaver['id'];
		$this->tester->seeInDatabase('fs_event', $event);
	}

	public function testInviteFullRegion()
	{
		$event = [
			'bezirk_id' => $this->region['id'],
			'location_id' => null,
			'public' => 0,
			'name' => 'name',
			'start' => '2018-09-01 12:00',
			'end' => '2018-09-30 12:00',
			'description' => 'd',
			'bot' => 0,
			'online' => 0,
		];
		$eventid = $this->gateway->addEvent($this->foodsaver['id'], $event);

		$usersInRegion = [$this->foodsaver['id']];
		$fs = $this->tester->createFoodsaver();
		$this->tester->addBezirkMember($this->region['id'], $fs['id']);
		$usersInRegion[] = $fs['id'];

		$this->gateway->inviteFullRegion($this->region['id'], $eventid, false);
		foreach ($usersInRegion as $fsid) {
			$this->tester->seeInDatabase('fs_foodsaver_has_event', ['foodsaver_id' => $fsid, 'event_id' => $eventid, 'status' => 0]);
		}

		$fs = $this->tester->createFoodsaver();
		$this->tester->addBezirkMember($this->childRegion['id'], $fs['id']);
		$usersInRegion[] = $fs['id'];

		$this->gateway->inviteFullRegion($this->region['id'], $eventid, true);
		foreach ($usersInRegion as $fsid) {
			$this->tester->seeInDatabase('fs_foodsaver_has_event', ['foodsaver_id' => $fsid, 'event_id' => $eventid, 'status' => 0]);
		}
	}
}
