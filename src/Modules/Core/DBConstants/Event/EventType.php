<?php

namespace Foodsharing\Modules\Core\DBConstants\Event;

class EventType
{
	public const MUMBLE = 0;
	public const OFFLINE = 1;

	public static function isOnline(int $eventType): bool
	{
		return $eventType == self::MUMBLE;
	}
}
