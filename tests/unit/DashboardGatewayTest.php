<?php

class DashboardGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Dashboard\DashboardGateway
	 */
	private $gateway;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Dashboard\DashboardGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->otherFoodsaver = $this->tester->createFoodsaver();

		foreach (range(1, 3) as $num) {
			$this->tester->haveInDatabase('fs_basket', [
				'foodsaver_id' => $this->foodsaver['id'],
				'status' => 1,
				'lat' => 52.520007, 'lon' => 13.404954 // Berlin
			]);
		}

		foreach (range(1, 3) as $num) {
			$this->tester->haveInDatabase('fs_basket', [
				'foodsaver_id' => $this->foodsaver['id'],
				'status' => 1,
				'lat' => 24.453884, 'lon' => 54.377344 // miles away from Berlin
			]);
		}
	}

	public function testGetNewestFoodBaskets()
	{
		$this->assertEquals(6, count($this->gateway->getNewestFoodbaskets(10)));
	}

	public function testListCloseBaskets()
	{
		$this->assertEquals(3, count($this->gateway->listCloseBaskets(
			$this->otherFoodsaver['id'],
			['lat' => 52.520007, 'lon' => 13.404954], // Berlin
			50)));
	}
}
