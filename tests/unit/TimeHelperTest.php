<?php

use Foodsharing\Utility\TimeHelper;

class TimeHelperTest extends \Codeception\Test\Unit
{
	protected UnitTester $tester;
	private ?TimeHelper $timeHelper;

	protected function _before()
	{
		$this->timeHelper = $this->tester->get(TimeHelper::class);
	}

	public function testNiceDate(): void
	{
		$testToday = $this->timeHelper->niceDate(time(), true);
		$this->assertStringStartsWith('heute', $testToday);
		$testTomorrow = $this->timeHelper->niceDate(time() + 60 * 60 * 24, true);
		$this->assertStringStartsNotWith('heute', $testTomorrow);
		$testTomorrow = $this->timeHelper->niceDate(time() + 60 * 60 * 24, false);
		$this->assertStringStartsWith('morgen', $testTomorrow);
	}
}
