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
	 * The number of up votes for this option. A value of null means that the poll is returned without results.
	 */
	public ?int $upvotes;

	/**
	 * The number of neutral votes for this option. Depending on the voting type is might not be used. A value of
	 * null means that the poll is returned without results.
	 */
	public ?int $neutralvotes;

	/**
	 * The number of down votes for this option. Depending on the voting type is might not be used. A value of null
	 * means that the poll is returned without results.
	 */
	public ?int $downvotes;

	public function __construct()
	{
		$this->pollId = -1;
		$this->optionIndex = -1;
		$this->text = '';
		$this->upvotes = null;
		$this->neutralvotes = null;
		$this->downvotes = null;
	}

	public static function create(
		int $pollId,
		int $optionIndex,
		string $text,
		int $upvotes = null,
		int $neutralvotes = null,
		int $downvotes = null
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
