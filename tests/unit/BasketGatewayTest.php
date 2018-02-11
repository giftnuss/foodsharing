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
		$pdo = new PDO('mysql:host=db;dbname=foodsharing', 'root', 'root', []);
		$this->gateway = new \Foodsharing\Modules\Basket\BasketGateway($pdo);
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
