<?php

// table fs_store_log

namespace Foodsharing\Modules\Core\DBConstants\Store;

class StoreLogAction
{
	public const REQUEST_TO_JOIN = 1;
	public const REQUEST_DECLINED = 2;
	public const REQUEST_APPROVED = 3;
	public const ADDED_WITHOUT_REQUEST = 4;
	public const MOVED_TO_JUMPER = 5;
	public const MOVED_TO_TEAM = 6;
	public const REMOVED_FROM_STORE = 7;
	public const LEFT_STORE = 8;
	public const MADE_STORE_MANAGER = 9;
	public const REMOVED_AS_STORE_MANAGER = 10;
	public const SIGN_UP_SLOT = 11;
	public const SIGN_OUT_SLOT = 12;
	public const REMOVED_FROM_SLOT = 13;
	public const SLOT_CONFIRMED = 14;
	public const DELETED_FROM_WALL = 15;
	public const REQUEST_CANCELLED = 16;
}
