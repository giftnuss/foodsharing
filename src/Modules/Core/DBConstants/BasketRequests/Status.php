<?php

// table fs_basket_anfrage

namespace Foodsharing\Modules\Core\DBConstants\BasketRequests;

/**
 * The current status of food basket requests
 * TINYINT(2) UNSIGNED | DEFAULT NULL.
 */
class Status
{
	/* request message sent */
	public const REQUESTED_MESSAGE_UNREAD = 0;
	/* request message sent */
	public const REQUESTED_MESSAGE_READ = 1;
	/* deleted due to picked up (bell menu) */
	public const DELETED_PICKED_UP = 2;
	public const DENIED = 3;
	public const NOT_PICKED_UP = 4;
	/* deleted due to not picked up or someone else picked up (bell menu) */
	public const DELETED_OTHER_REASON = 5;
	/* unused, removed in code, might still be in DB */
	public const FOLLOWED = 9;
	/* request pop up opened */
	public const REQESTED = 10;
}
