<?php

namespace Foodsharing\Modules\Bell;

/**
 * This class triggers bell updates. If a class needs to update its bells when they get outdated (expire), it
 * can subscribe to this bellUpdater to be called when a bell is expired. For this, it needs to implement
 * the BellUpdaterInterface.
 */
class BellUpdateTrigger
{
	/**
	 * @var BellUpdaterInterface[]
	 */
	private $subscribedBellUpdaters = [];

	public function subscribe(BellUpdaterInterface $bellUpdater): void
	{
		$this->subscribedBellUpdaters[] = $bellUpdater;
	}

	public function triggerUpdate(): void
	{
		foreach ($this->subscribedBellUpdaters as $bellUpdater) {
			$bellUpdater->updateExpiredBells();
		}
	}
}
