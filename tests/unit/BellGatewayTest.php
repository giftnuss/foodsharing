<?php

class BellGatewayTest extends \Codeception\Test\Unit
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
		$bellData = \Foodsharing\Modules\Bell\DTO\Bell::create(
			'first bell title',
			$this->faker->text(50),
			'',
			[''],
			[],
			'',
			1
		);
		/* addBell accepts different inputs: $id, [$id, $id], [['id' => $id]] */
		$this->gateway->addBell([$user1, $user2], $bellData);
		$bellId = $this->tester->grabFromDatabase('fs_bell', 'id', ['name' => $bellData->title, 'body' => $bellData->body]);
		$this->tester->seeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $user1['id'], 'bell_id' => $bellId, 'seen' => 0]);
		$this->tester->seeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $user2['id'], 'bell_id' => $bellId, 'seen' => 0]);

		$bellData->title = 'second bell title';
		$this->gateway->addBell([$user1, $user2], $bellData);
		$bellId = $this->tester->grabFromDatabase('fs_bell', 'id', ['name' => $bellData->title, 'body' => $bellData->body]);
		$this->tester->seeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $user1['id'], 'bell_id' => $bellId, 'seen' => 0]);
		$this->tester->seeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $user2['id'], 'bell_id' => $bellId, 'seen' => 0]);
	}

	public function testRemoveBellWorksIfIdentifierIsCorrect()
	{
		$this->tester->clearTable('fs_bell');
		$this->tester->clearTable('fs_foodsaver_has_bell');

		$user1 = $this->tester->createFoodsaver();
		$user2 = $this->tester->createFoodsaver();

		$this->tester->addBells([$user1, $user2], ['identifier' => 'my-custom-identifier']);
		$this->tester->seeNumRecords(1, 'fs_bell');
		$this->tester->seeNumRecords(2, 'fs_foodsaver_has_bell');

		$this->gateway->delBellsByIdentifier('my-custom-identifier');

		$this->tester->seeNumRecords(0, 'fs_bell');
		$this->tester->seeNumRecords(0, 'fs_foodsaver_has_bell');
	}

	public function testRemoveBellDoesNotWorkIfIdentifierIsIncorrect()
	{
		$this->tester->clearTable('fs_bell');
		$this->tester->clearTable('fs_foodsaver_has_bell');

		$user1 = $this->tester->createFoodsaver();
		$user2 = $this->tester->createFoodsaver();

		$this->tester->addBells([$user1, $user2], ['identifier' => 'my-custom-identifier']);
		$this->tester->seeNumRecords(1, 'fs_bell');
		$this->tester->seeNumRecords(2, 'fs_foodsaver_has_bell');

		$this->gateway->delBellsByIdentifier('my-custom-wrong-identifier');

		$this->tester->seeNumRecords(1, 'fs_bell');
		$this->tester->seeNumRecords(2, 'fs_foodsaver_has_bell');
	}

	public function testGetOneByIdentifier()
	{
		$this->tester->clearTable('fs_bell');

		$user1 = $this->tester->createFoodsaver();
		$user2 = $this->tester->createFoodsaver();

		$identifier = 'my-custom-identifier';

		$this->tester->addBells([$user1, $user2], ['identifier' => $identifier]);

		$bellId = $this->gateway->getOneByIdentifier($identifier);

		$this->tester->seeInDatabase('fs_bell', ['id' => $bellId, 'identifier' => $identifier]);
	}

	public function testUpdateBell()
	{
		$this->tester->clearTable('fs_bell');

		$user1 = $this->tester->createFoodsaver();
		$user2 = $this->tester->createFoodsaver();

		$bellData = \Foodsharing\Modules\Bell\DTO\Bell::create(
			'title',
			$this->faker->text(50),
			'some-icon',
			[],
			[],
			'some-identifier',
			$closable = 0
		);

		$this->gateway->addBell([$user1, $user2], $bellData);
		$bellId = $this->tester->grabFromDatabase('fs_bell', 'id', ['name' => $bellData->title, 'body' => $bellData->body]);

		$updatedData = [
			'name' => 'updated title',
			'body' => $this->faker->text(50),
			'icon' => 'some-updated-icon',
			'identifier' => 'some-updated-identifier',
			'closeable' => 1
		];

		$this->gateway->updateBell($bellId, $updatedData);

		$this->tester->seeInDatabase('fs_bell', $updatedData);
	}
}
