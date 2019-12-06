<?php

use Foodsharing\Modules\Buddy\BuddyGateway;

class BuddyGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var BuddyGateway|null
	 */
	private $gateway;
	private $foodsaver;
	private $otherFoodsaver;

	protected function _before()
	{
		$this->gateway = $this->tester->get(BuddyGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->otherFoodsaver = $this->tester->createFoodsaver();
		$this->basketIds = [];
	}

	protected function _after()
	{
	}

	public function testRequestAndConfim()
	{
		$this->gateway->buddyRequest($this->foodsaver['id'], $this->otherFoodsaver['id']);
		$this->tester->seeInDatabase('fs_buddy', [
			'foodsaver_id' => $this->otherFoodsaver['id'],
			'buddy_id' => $this->foodsaver['id'],
			'confirmed' => 0
		]);

		$this->gateway->confirmBuddy($this->foodsaver['id'], $this->otherFoodsaver['id']);
		$this->tester->seeInDatabase('fs_buddy', [
			'foodsaver_id' => $this->foodsaver['id'],
			'buddy_id' => $this->otherFoodsaver['id'],
			'confirmed' => 1
		]);
		$this->tester->seeInDatabase('fs_buddy', [
			'foodsaver_id' => $this->otherFoodsaver['id'],
			'buddy_id' => $this->foodsaver['id'],
			'confirmed' => 1
		]);
	}
}
