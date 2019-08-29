<?php

// table fs_quiz_session

namespace Foodsharing\Modules\Core\DBConstants\Quiz;

/**
 * only valid for working groups
 * TINYINT(2) | DEFAULT NULL.
 */
class SessionStatus
{
	public const RUNNING = 0;
	public const PASSED = 1;
	public const FAILED = 2;
}
