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

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Event\EventGateway::class);
		$this->faker = Faker\Factory::create('de_DE');
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
}
