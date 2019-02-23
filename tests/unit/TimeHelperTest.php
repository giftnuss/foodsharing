<?php

class TimeHelperTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var \Foodsharing\Helpers\TimeHelper|null
	 */
	private $timeHelper;

	protected function _before()
	{
		$this->timeHelper = $this->tester->get(\Foodsharing\Helpers\TimeHelper::class);
	}

	public function testNiceDate(): void
	{
		$testToday = $this->timeHelper->niceDate(time(), true);
		$this->assertStringStartsWith('today', $testToday);
		$testTomorrow = $this->timeHelper->niceDate(time() + 60 * 60 * 24, true);
		$this->assertStringStartsNotWith('today', $testTomorrow);
		$testTomorrow = $this->timeHelper->niceDate(time() + 60 * 60 * 24, false);
		$this->assertStringStartsWith('tomorrow', $testTomorrow);
	}
}
