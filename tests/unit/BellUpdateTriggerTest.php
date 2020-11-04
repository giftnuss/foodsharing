<?php

use Foodsharing\Modules\Bell\BellUpdaterInterface;
use Foodsharing\Modules\Bell\BellUpdateTrigger;
use PHPUnit\Framework\MockObject\MockObject;

class BellUpdateTriggerTest extends \Codeception\Test\Unit
{
	protected UnitTester $tester;
	private BellUpdateTrigger $bellUpdateTrigger;

	protected function _before()
	{
		$this->bellUpdateTrigger = $this->tester->get(BellUpdateTrigger::class);
	}

	protected function _after()
	{
	}

	// tests
	public function testBellUpdateGetsTriggered()
	{
		/**
		 * @var BellUpdaterInterface|MockObject
		 */
		$bellUpdater = $this->getMockBuilder(BellUpdaterInterface::class)->getMock();
		$bellUpdater->expects($this->once())->method('updateExpiredBells');

		$this->bellUpdateTrigger->subscribe($bellUpdater);
		$this->bellUpdateTrigger->triggerUpdate();
	}
}
