<?php


class BasketGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Basket\BasketGateway
	 */
	private $gateway;

	protected function _before()
	{
		$this->gateway = $this->tester->container()->get(\Foodsharing\Modules\Basket\BasketGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
	}

	protected function _after()
	{
	}

	public function testGetUpdateCount()
	{
		$this->assertEquals(0, $this->gateway->getUpdateCount($this->foodsaver['id']));
	}
}
