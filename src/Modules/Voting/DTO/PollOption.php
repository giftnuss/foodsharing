<?php

namespace Foodsharing\Modules\Voting\DTO;

/**
 * Class that represents one option of a poll.
 */
class PollOption
{
	/**
	 * Id of the poll to which this option belongs.
	 */
	public int $pollId;

	/**
	 * Index of this option in the poll.
	 */
	public int $optionIndex;

	/**
	 * A short description of the option.
	 */
	public string $text;

	/**
	 * The number of up votes for this option.
	 */
	public int $upvotes;

	/**
	 * The number of neutral votes for this option. Depending on the voting type is might not be used.
	 */
	public int $neutralvotes;

	/**
	 * The number of down votes for this option. Depending on the voting type is might not be used.
	 */
	public int $downvotes;

	public static function create(
		int $pollId,
		int $optionIndex,
		string $text,
		int $upvotes = 0,
		int $neutralvotes = 0,
		int $downvotes = 0
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
