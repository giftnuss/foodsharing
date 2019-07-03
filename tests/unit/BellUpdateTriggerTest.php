<?php

use Foodsharing\Modules\Bell\BellUpdateTrigger;
use Foodsharing\Modules\Bell\BellUpdaterInterface;

class BellUpdateTriggerTest extends \Codeception\Test\Unit
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * @var BellUpdateTrigger
	 */
	private $bellUpdateTrigger;

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
		 * @var BellUpdaterInterface|PHPUnit\Framework\MockObject\MockObject
		 */
		$bellUpdater = $this->getMockBuilder(BellUpdaterInterface::class)->getMock();
		$bellUpdater->expects($this->once())->method('updateExpiredBells');

		$this->bellUpdateTrigger->subscribe($bellUpdater);
		$this->bellUpdateTrigger->triggerUpdate();
	}
}
