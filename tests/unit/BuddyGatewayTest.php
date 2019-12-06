<?php

use Foodsharing\Modules\Buddy\BasketGateway;

class BuddyGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Buddy\BuddyGateway|null
	 */
	private $gateway;
	private $foodsaver;
	private $otherFoodsaver;

	protected function _before()
	{
		$this->gateway = $this->tester->get(BasketGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->otherFoodsaver = $this->tester->createFoodsaver();
		$this->basketIds = [];
	}

	protected function _after()
	{
	}

	public function testGetUpdateCount()
	{
		$this->gateway->buddyRequest($this->foodsaver['id'], $this->otherFoodsaver);
		$this->tester->seeInDatabase('fs_buddy', [
			'foodsaver_id' => $this->otherFoodsaver['id'],
			'buddy_id' => $this->foodsaver['id'],
			'confirmed' => 0
		]);

		$this->gateway->confirmBuddy($this->foodsaver['id'], $this->otherFoodsaver);
		$this->tester->seeInDatabase('fs_buddy', [
			'foodsaver_id' => $this->foodsaver['id'],
			'buddy_id' => $this->otherFoodsaver['id'],
			'confirmed' => 0
		]);
		$this->tester->seeInDatabase('fs_buddy', [
			'foodsaver_id' => $this->otherFoodsaver['id'],
			'buddy_id' => $this->foodsaver['id'],
			'confirmed' => 0
		]);
	}
}
