<?php

class BasketGatewayTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Modules\Basket\BasketGateway|null
	 */
	private $gateway;
	private $foodsaver;
	private $otherFoodsaver;
	/**
	 * @var array|null
	 */
	private $basketIds;

	protected function _before()
	{
		$this->gateway = $this->tester->get(\Foodsharing\Modules\Basket\BasketGateway::class);
		$this->foodsaver = $this->tester->createFoodsaver();
		$this->otherFoodsaver = $this->tester->createFoodsaver();
		$this->basketIds = [];

		foreach (range(1, 10) as $num) {
			$basketId = $this->tester->haveInDatabase('fs_basket', [
				'foodsaver_id' => $this->foodsaver['id']
			]);
			$this->tester->haveInDatabase('fs_basket_anfrage', [
				'basket_id' => $basketId,
				'foodsaver_id' => $this->foodsaver['id'],
				'status' => 0
			]);
			$this->basketIds[] = $basketId;
		}

		foreach (range(1, 3) as $num) {
			$this->tester->haveInDatabase('fs_basket', [
				'foodsaver_id' => $this->foodsaver['id'],
				'status' => 1,
				'until' => date('Y-m-d', time() + 86400),
				'lat' => 52.520007, 'lon' => 13.404954 // Berlin
			]);
		}

		foreach (range(1, 3) as $num) {
			$this->tester->haveInDatabase('fs_basket', [
				'foodsaver_id' => $this->foodsaver['id'],
				'status' => 1,
				'until' => date('Y-m-d', time() + 86400),
				'lat' => 24.453884, 'lon' => 54.377344 // miles away from Berlin
			]);
		}
	}

	protected function _after()
	{
	}

	public function testGetUpdateCount()
	{
		$this->assertEquals(10, $this->gateway->getUpdateCount($this->foodsaver['id']));
	}

	public function testGetBasket()
	{
		//existing basket
		$result = $this->gateway->getBasket($this->basketIds[0]);
		$this->assertIsArray($result);

		//non-existing basket
		$this->assertEquals(false, $this->gateway->getBasket(99999));
	}

	public function testListNewestBaskets()
	{
		$this->assertCount(6, $this->gateway->listNewestBaskets());
	}

	public function testListNearbyBasketsByDistance()
	{
		$this->assertCount(
			3,
			$this->gateway->listNearbyBasketsByDistance(
				$this->otherFoodsaver['id'],
				['lat' => 52.520007, 'lon' => 13.404954], // Berlin
				50
			)
		);
	}
}
