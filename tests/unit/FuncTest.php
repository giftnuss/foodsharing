<?php

use Foodsharing\Lib\Func;

class FuncTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var Func
	 */
	private $func;

	protected function _before()
	{
		$this->func = $this->tester->get(\Foodsharing\Lib\Func::class);
	}

	public function testNiceDate(): void
	{
		$testToday = $this->func->niceDate(time(), true);
		$this->assertStringStartsWith('today', $testToday);
		$testTomorrow = $this->func->niceDate(time() + 60 * 60 * 24, true);
		$this->assertStringStartsNotWith('today', $testTomorrow);
		$testTomorrow = $this->func->niceDate(time() + 60 * 60 * 24, false);
		$this->assertStringStartsWith('tomorrow', $testTomorrow);
	}
}
