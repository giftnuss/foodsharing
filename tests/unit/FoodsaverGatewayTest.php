<?php

class FoodsaverGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Foodsaver\FoodsaverGateway
	 */
	private $gateway;
	private $foodsaver;
	private $region;
	private $regionMember;
	private $regionAdmin;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Foodsaver\FoodsaverGateway::class);
		
		$this->foodsaver = $this->tester->createFoodsaver();

		$this->region = $this->tester->createRegion('TestRegion');
		$this->regionMember = $this->tester->createFoodsaver();
		$this->tester->addBezirkMember($this->region['id'], $this->regionMember['id']);
		$this->regionAdmin = $this->tester->createAmbassador();
		$this->tester->addBezirkMember($this->region['id'], $this->regionAdmin['id']);
		$this->tester->addBezirkAdmin($this->region['id'], $this->regionAdmin['id']);
	}

	public function testUpdateProfile()
	{
		$this->gateway->updateProfile($this->foodsaver['id'], ['anschrift' => 'my new street']);
		$this->tester->seeInDatabase('fs_foodsaver', [
			'id' => $this->foodsaver['id'],
			'anschrift' => 'my new street'
		]);

		// strips tags
		$this->gateway->updateProfile($this->foodsaver['id'], ['anschrift' => '<script>my new street']);
		$this->tester->seeInDatabase('fs_foodsaver', [
			'id' => $this->foodsaver['id'],
			'anschrift' => 'my new street'
		]);
	}

	public function testGetPhoto()
	{
		$this->gateway->updatePhoto($this->foodsaver['id'], 'mypicture.png');
		$this->tester->assertEquals(
			$this->gateway->getPhoto($this->foodsaver['id']),
			'mypicture.png'
		);
	}
	
	public function testUpdateGroupMembersByAdditionWhileLeavingAdmin() {
		$newFoodsaver = $this->tester->createFoodsaver();
		
		$fsIds = [$newFoodsaver['id'], $this->regionMember['id']];
		$updates = $this->gateway->updateGroupMembers($this->region['id'], $fsIds, true);
		
		$this->tester->seeInDatabase(
			'fs_foodsaver_has_bezirk',
			['foodsaver_id' => $newFoodsaver['id'], 'bezirk_id' => $this->region['id']]
		);
		$this->tester->assertEquals(2, $updates[0], 'Wrong number of inserts!');
		$this->tester->assertEquals(0, $updates[1], 'Wrong number of deletions!');
	}
	
	public function testUpdateGroupMembersByAdditionWhileNotLeavingAdmin() {
		$newFoodsaver = $this->tester->createFoodsaver();
		
		$fsIds = [$newFoodsaver['id'], $this->regionMember['id']];
		$updates = $this->gateway->updateGroupMembers($this->region['id'], $fsIds, false);
		
		$this->tester->seeInDatabase(
			'fs_foodsaver_has_bezirk',
			['foodsaver_id' => $newFoodsaver['id'], 'bezirk_id' => $this->region['id']]
		);
		$this->tester->assertEquals(1, $updates[0], 'Wrong number of inserts!');
		$this->tester->assertEquals(1, $updates[1], 'Wrong number of deletions!');
	}
	
	public function testUpdateGroupMembersByDeletionWhileLeavingAdmin() {
		$fsIds = [];
		$updates = $this->gateway->updateGroupMembers($this->region['id'], $fsIds, true);
		
		$this->tester->dontSeeInDatabase(
			'fs_foodsaver_has_bezirk',
			['foodsaver_id' => $this->regionMember['id'], 'bezirk_id' => $this->region['id']]
		);
		$this->tester->assertEquals(0, $updates[0], 'Wrong number of inserts!');
		$this->tester->assertEquals(1, $updates[1], 'Wrong number of deletions!');
	}
	
	public function testUpdateGroupMembersByDeletionWhileNotLeavingAdmin() {
		$fsIds = [];
		$updates = $this->gateway->updateGroupMembers($this->region['id'], $fsIds, false);
		
		$this->tester->dontSeeInDatabase(
			'fs_foodsaver_has_bezirk',
			['foodsaver_id' => $this->regionMember['id'], 'bezirk_id' => $this->region['id']]
		);
		$this->tester->assertEquals(0, $updates[0], 'Wrong number of inserts!');
		$this->tester->assertEquals(2, $updates[1], 'Wrong number of deletions!');
	}
}
