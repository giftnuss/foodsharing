<?php

namespace Foodsharing\Modules\Voting\DTO;

/**
 * Class that represents one option of a poll.
 */
class PollOption
{
	/**
	 * @var int
	 *
	 * Id of the poll to which this option belongs.
	 */
	public $pollId;

	/**
	 * @var int
	 *
	 * Index of this option in the poll.
	 */
	public $optionIndex;

	/**
	 * @var string
	 *
	 * A short description of the option.
	 */
	public $text;

	/**
	 * @var int
	 *
	 * The number of votes for this option. Depending on the voting type this can include up and down votes.
	 */
	public $votes;

	public function __construct(
		int $pollId,
		int $optionIndex,
		string $text,
		int $votes
	) {
		$this->pollId = $pollId;
		$this->optionIndex = $optionIndex;
		$this->text = $text;
		$this->votes = $votes;
	}
}
