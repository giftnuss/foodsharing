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
	}

	public function testGetNewestFoodBaskets()
	{
		$this->assertEquals([], $this->gateway->getNewestFoodbaskets(10));
	}

	public function testListCloseBaskets()
	{
		$this->assertEquals([], $this->gateway->listCloseBaskets($this->foodsaver['id'], ['lat' => 3434, 'lon' => 2323], 50));
	}
}
