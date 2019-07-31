<?php

namespace Foodsharing\Modules\Core\DBConstants\Quiz;

class QuizStatus
{
	/**
	 * User never tried to solve the quiz.
	 *
	 * @var int
	 */
	public const NEVER_TRIED = 0;

	/**
	 * There is a quiz solving in progress.
	 *
	 * @var int
	 */
	public const RUNNING = 1;

	/**
	 * Quiz had been passed.
	 *
	 * @var int
	 */
	public const PASSED = 2;

	/**
	 * User failed to pass the quiz. The number of failures is less than three.
	 *
	 * @var int
	 */
	public const FAILED = 3;

	/**
	 * User failed to solve the quiz three times. There is a pause of 30 days before the next try.
	 *
	 * @var int
	 */
	public const PAUSE = 4;

	/**
	 * A 30-days pause after failing three times elapsed. The quiz is open for another two tries to solve.
	 *
	 * @var int
	 */
	public const PAUSE_ELAPSED = 5;

	/**
	 * All tries to solve the quiz were unsuccessful.
	 *
	 * @var int
	 */
	public const DISQUALIFIED = 6;
}
