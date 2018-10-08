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
		/* addBell accepts different inputs: $id, [$id, $id], [['id' => $id]] */
		$title = 'title';
		$body = $this->faker->text(50);
		$this->gateway->addBell([$user1, $user2], $title, $body, '', [''], [], '', 1);
		$bid = $this->tester->grabFromDatabase('fs_bell', 'id', ['name' => $title, 'body' => $body]);
		$this->tester->seeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $user1['id'], 'bell_id' => $bid, 'seen' => 0]);
		$this->tester->seeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $user2['id'], 'bell_id' => $bid, 'seen' => 0]);
		$title = 'title_zwei';
		$this->gateway->addBell([$user1, $user2], $title, $body, '', [''], [], '', 0);
		$bid = $this->tester->grabFromDatabase('fs_bell', 'id', ['name' => $title, 'body' => $body]);
		$this->tester->seeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $user1['id'], 'bell_id' => $bid, 'seen' => 0]);
		$this->tester->seeInDatabase('fs_foodsaver_has_bell', ['foodsaver_id' => $user2['id'], 'bell_id' => $bid, 'seen' => 0]);
	}

	public function testRemoveBell()
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

		$title = 'title';
		$body = $this->faker->text(50);
		$icon = 'some-icon';
		$identifier = 'some-identifier';
		$closable = 0;

		$this->gateway->addBell([$user1, $user2], $title, $body, $icon, [], [], $identifier, $closable);
		$bellId = $this->tester->grabFromDatabase('fs_bell', 'id', ['name' => $title, 'body' => $body]);

		$updatedTitle = 'updated title';
		$updatedBody = $this->faker->text(50);
		$updatedIcon = 'some-updated-icon';
		$updatedIdentifier = 'some-updated-identifier';
		$updatedClosable = 1;

		$this->gateway->updateBell($bellId, $updatedTitle, $updatedBody, $updatedIcon, [], [], $updatedIdentifier, $updatedClosable);

		$this->tester->seeInDatabase(
			'fs_bell',
			array(
				'id' => $bellId,
				'name' => $updatedTitle,
				'body' => $updatedBody,
				'icon' => $updatedIcon,
				'identifier' => $updatedIdentifier,
				'closeable' => $updatedClosable
			)
		);
	}
}
