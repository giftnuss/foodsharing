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
	 * Associative array that maps the possible values to the number of counted votes. Value of -1 mean that
	 * the poll is returned without results.
	 */
	public array $values;

	public function __construct()
	{
		$this->pollId = -1;
		$this->optionIndex = -1;
		$this->text = '';
		$this->values = [];
	}

	public static function create(
		int $pollId,
		int $optionIndex,
		string $text,
		array $values
	) {
		$option = new PollOption();
		$option->pollId = $pollId;
		$option->optionIndex = $optionIndex;
		$option->text = $text;
		$option->values = $values;

		return $option;
	}
}
