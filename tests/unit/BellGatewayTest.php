<?php


class EventGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Bell\BellGateway
	 */
	private $gateway;

	/**
	 * @var Faker\Generator
	 */
	private $faker;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Bell\BellGateway::class);
		$this->faker = Faker\Factory::create('de_DE');
	}

	public function testAddBell()
	{
		$user1 = $this->tester->createFoodsaver();
		$user2 = $this->tester->createFoodsaver();
		/* addBell accepts different inputs: $id, [$id, $id], [['id' => $id]] */
		$title = 'title';
		$body = $this->faker->text(50);
		$this->gateway->addBell([$user1, $user2], 'title', $body, '', '', false, '', 1);
		$bid = $this->tester->grabFromDatabase('fs_bell', 'id', ['name' => $title, 'body' => $body]);
		$this->tester->seeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $user1['id'], 'bell_id' => $bid, 'seen' => 0]);
		$this->tester->seeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $user2['id'], 'bell_id' => $bid, 'seen' => 0]);
	}
}
