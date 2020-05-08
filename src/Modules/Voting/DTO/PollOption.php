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
	 * Id of the poll to which this option belongs
	 */
	public $pollId;

	/**
	 * @var int
	 *
	 * Index of this option in the poll
	 */
	public $optionIndex;

	/**
	 * @var string
	 *
	 * A short description of the option
	 */
	public $text;

	/**
	 * @var int
	 *
	 * The number of up votes for this option
	 */
	public $upvotes;

	/**
	 * @var int
	 *
	 * The number of neutral votes for this option. Depending on the voting type is might not be used.
	 */
	public $neutralvotes;

	/**
	 * @var int
	 *
	 * The number of down votes for this option. Depending on the voting type is might not be used.
	 */
	public $downvotes;

	public static function create(
		int $pollId,
		int $optionIndex,
		string $text,
		int $upvotes,
		int $neutralvotes,
		int $downvotes
	) {
		$option = new PollOption();
		$option->pollId = $pollId;
		$option->optionIndex = $optionIndex;
		$option->text = $text;
		$option->upvotes = $upvotes;
		$option->neutralvotes = $neutralvotes;
		$option->downvotes = $downvotes;

		return $option;
	}
}
