<?php

namespace Foodsharing\Modules\Core\DBConstants\Quiz;

class QuizStatus
{
	public const NEVER_TRIED = 0;
	public const RUNNING = 1;
	public const PASSED = 2;
	public const FAILED = 3;	// Less than three failures
	public const PAUSE = 4;	// Three failures lead to a 30 days learning pause
	public const PAUSE_ELAPSED = 5;	// After 30 days there are two additional tries
	public const DISQUALIFIED = 6;	// Failed another two times
}
