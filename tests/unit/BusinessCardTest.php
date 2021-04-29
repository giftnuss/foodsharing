<?php

use Codeception\Test\Unit;
use Foodsharing\Modules\BusinessCard\BusinessCardControl;

class BusinessCardTest extends Unit
{
	protected UnitTester $tester;

	private BusinessCardControl $business_card;

	protected function _before()
	{
		$this->business_card = $this->tester->get(BusinessCardControl::class);
	}

	protected function _after()
	{
	}

	/**
	 * @dataProvider pageProvider
	 */
	public function testPositionStreetNumber(string $address, int $index)
	{
		$out = $this->invokePrivateMethod([$address]);
		$this->assertEquals(
			$index,
			$out
		);
	}

	/**
	 * Helper method for accessing the private function 'index_of_first_number'.
	 */
	private function invokePrivateMethod(array $parameters = [])
	{
		$reflection = new \ReflectionClass(get_class($this->business_card));
		$method = $reflection->getMethod('index_of_first_number');
		$method->setAccessible(true);

		return $method->invokeArgs($this->business_card, $parameters);
	}

	/**
	 * Provides the test data for {@see testPositionStreetNumber}.
	 */
	public function pageProvider()
	{
		return [
			['Straße 1', 7],
			['Sträßchän 1', 10],
			['Land Landggg Langggg adsflkja dsfölkajsd flökasjdf dddGünter-Lechner-Allee 111', 75],
			['Teststraße-∫∫∫∫∫∫∫∫∫∫∫∫∫∫∫∫∫∫∫-abcdefgxyz 123', 42],
			['Teststraße-∫∫∫∫∫∫∫∫∫∫∫∫∫∫∫∫∫∫∫-abcdefgxyzabcdefghijklmnopq 123', 59]
		];
	}
}
