<?php

namespace Foodsharing\Modules\Bell;

/**
 * Implement this interface if you want to subscribe to the BellUpdateTrigger.
 */
interface BellUpdaterInterface
{
	/**
	 * This method gets called by the BellUpdateTrigger when a Bell is found that reached its expiration date and
	 * needs to be updated. Implement a function that gets all of your expired bells from the database and updates
	 * them.
	 *
	 * An update can be anything: A bell can also be removed when it got outdated.
	 */
	public function updateExpiredBells(): void;
}
