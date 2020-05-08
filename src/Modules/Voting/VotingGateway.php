<?php

namespace Foodsharing\Modules\Voting;

use DateTime;
use Exception;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Voting\DTO\Poll;
use Foodsharing\Modules\Voting\DTO\PollOption;

//TODO: can the fs_fodsaver_has_poll entries be deleted after the poll is finished? should polls ever be deleted?
class VotingGateway extends BaseGateway
{
	/**
	 * Returns the detailed data of a poll.
	 *
	 * @param int $pollId a valid id of a poll
	 * @return Poll the poll object
	 * @throws Exception if the poll with the given id does not exist
	 */
	public function getPoll(int $pollId): Poll
	{
		$data = $this->db->fetchByCriteria('fs_poll',
			['region_id', 'scope', 'name', 'description', 'type', 'start', 'end'],
			['id' => $pollId]
		);
		if (empty($data)) {
			throw new Exception('poll does not exist');
		}

		return new Poll($pollId, $data['name'], $data['description'],
			new DateTime($data['start']), new DateTime($data['end']),
			$data['region_id'], $data['scope'], $data['type']);
	}

	/**
	 * Returns all options of a poll.
	 *
	 * @param int $pollId a valid id of a poll
	 * @return array multiple {@link PollOption} objects
	 * @throws Exception if the poll does not exist
	 */
	public function getOptions(int $pollId): array
	{
		$data = $this->db->fetchByCriteria('fs_poll_has_option',
			['option', 'text', 'upvotes', 'neutralvotes', 'downvotes'],
			['poll_id' => $pollId]
		);

		return array_map(function ($x) use ($pollId) {
			return new PollOption($pollId, $x['option'], $x['text'], $x['upvotes'], $x['neutralvotes'], $x['downvotes']);
		}, $data);
	}

	/**
	 * Returns whether a user has already voted in a specific poll.
	 *
	 * @param int $pollId a valid id of a poll
	 * @param int $userId a valid user id
	 * @return bool whether the user has already votes in that poll
	 * @throws Exception if the poll does not exist or the user is not allowed to vote in that poll
	 */
	public function hasUserVoted(int $pollId, int $userId): bool
	{
		return $this->db->fetchValueByCriteria('fs_foodsaver_has_poll', 'has_voted', [
				'user_id' => $userId,
				'poll_id' => $pollId
			]) === 1;
	}

	/**
	 * Updates the vote counters of a poll and updates that the user has voted.
	 *
	 * @param int $pollId a valid id of a poll
	 * @param int $userId a valid user id
	 * @param array $options the vote (+1, -1, 0) for each option
	 * @throws Exception if the poll does not exist
	 */
	public function vote(int $pollId, int $userId, array $options): void
	{
		$columns = ['upvotes', 'neutralvotes', 'downvotes'];
		//TODO: this needs locks
		$this->db->beginTransaction();

		foreach ($options as $option => $vote) {
			// increment one of the columns depending on the vote for this option
			$column = $columns[$vote + 1];
			$this->db->update('fs_poll_has_option',
				[$column => $column . '+1'],
				['poll_id' => $pollId, 'option' => $option]);
		}

		$this->db->update('fs_foodsaver_has_poll', ['has_voted' => 1], ['user_id' => $userId]);
		$this->db->commit();
	}

	/**
	 * Inserts a new poll.
	 *
	 * @param Poll $poll a valid poll object
	 * @param array $options a set of PollOptions
	 * @param array $userIds the ids of all users that will be allowed to vote
	 * @return int the id of the created poll
	 * @throws Exception
	 */
	public function insertPoll(Poll $poll, array $options, array $userIds): int
	{
		// insert the poll
		$pollId = $this->db->insert('fs_poll', [
			'region_id' => $poll->regionId,
			'scope' => $poll->scope,
			'name' => $poll->name,
			'description' => $poll->description,
			'type' => $poll->type,
			'start' => $this->db->now(),
			'end' => $poll->endDate
		]);

		// insert all options
		foreach ($options as $option) {
			if (!($option instanceof PollOption)) {
				throw new Exception('unexpected object type for the poll option');
			}

			$this->db->insert('fs_poll_has_options', [
				'poll_id' => $pollId,
				'option' => $option->optionIndex,
				'option_text' => $option->text,
				'upvotes' => 0,
				'neutralvotes' => 0,
				'downvotes' => 0
			]);
		}

		// insert all voters
		foreach ($userIds as $user) {
			$this->db->insert('fs_foodsaver_has_poll', [
				'foodsaver_id' => $user,
				'poll_id' => $pollId,
				'has_voted' => 0
				//TODO: is the timestamp `time` automatically inserted?
			]);
		}

		return $pollId;
	}
}
