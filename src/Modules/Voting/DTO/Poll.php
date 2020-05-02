<?php

namespace Foodsharing\Modules\Voting\DTO;

use DateTime;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingType;

/**
 * Class that represents a voting or election process.
 */
class Poll
{
	/**
	 * @var int
	 *
	 * Unique identifier of this poll.
	 */
	public $id;

	/**
	 * @var string
	 *
	 * A short description of the poll that can serve as a title.
	 */
	public $name;

	/**
	 * @var string
	 *
	 * A more detailed description of the topic of this poll.
	 */
	public $description;

	/**
	 * @var DateTime
	 *
	 * The date at which this poll began.
	 */
	public $startDate;

	/**
	 * @var DateTime
	 *
	 * The date at which this poll will end or has ended.
	 */
	public $endDate;

	/**
	 * @var int
	 *
	 * Identifier of the region or work group in which this poll takes place. Only members of that region are allowed
	 * to vote.
	 */
	public $regionId;

	/**
	 * @var int
	 *
	 * The scope is an additional constraint defining which user groups are allowed to vote.
	 */
	public $scope;

	/**
	 * @var int
	 *
	 * The type defines how users can vote. Different types allow to vote for one or multiple choices or allow a score
	 * voting. See {@link VotingType}.
	 */
	public $type;

	/**
	 * @var array<int,string>
	 *
	 * The options of this poll.
	 */
	public $options;

	public function __construct(
		int $id,
		string $name,
		string $description,
		DateTime $startDate,
		DateTime $endDate,
		int $regionId,
		int $scope,
		int $type,
		array $options
	) {
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->startDate = $startDate;
		$this->endDate = $endDate;
		$this->regionId = $regionId;
		$this->scope = $scope;
		$this->type = $type;
		$this->options = $options;
	}
}
