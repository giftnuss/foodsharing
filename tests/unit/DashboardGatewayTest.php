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
		$this->gateway = $this->tester->container()->get(\Foodsharing\Modules\Dashboard\DashboardGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
	}

	protected function _after()
	{
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
