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
	 * @param int $pollId
	 * @return Poll
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
	 * @param int $pollId
	 * @return array multiple {@link PollOption} objects
	 * @throws Exception if the poll does not exist
	 */
	public function getOptions(int $pollId): array
	{
		$data = $this->db->fetchByCriteria('fs_poll_has_option',
			['option', 'text', 'votes'],
			['poll_id' => $pollId]
		);

		return array_map(function ($x) use ($pollId) {
			return new PollOption($pollId, $x['option'], $x['text'], $x['votes']);
		}, $data);
	}

	/**
	 * Returns whether a user has already voted in a specific poll.
	 *
	 * @param int $pollId
	 * @param int $userId
	 * @return bool
	 * @throws Exception if the poll does not exist or the user is not allowed to vote in that poll
	 */
	public function hasUserVoted(int $pollId, int $userId): bool
	{
		return $this->db->fetchValueByCriteria('fs_foodsaver_has_poll', 'has_voted', [
				'user_id' => $userId,
				'id' => $pollId
			]) == 1;
	}

	/**
	 * Updates the vote counters of a poll and updates that the user has voted.
	 *
	 * @param int $pollId
	 * @param int $userId
	 * @param array $options the vote (+1, -1, 0) for each option
	 * @throws Exception if the poll does not exist
	 */
	public function vote(int $pollId, int $userId, array $options): void
	{
		//TODO: this needs locks
		$this->db->beginTransaction();

		foreach ($options as $option => $vote) {
			if ($vote !== 0) {
				$increment = ($vote > 0) ? '+1' : '-1';
				$this->db->update('fs_poll_has_option',
					['votes = votes' . $increment],
					['poll_id' => $pollId, 'option' => $option]);
			}
		}

		$this->db->update('fs_foodsaver_has_poll', ['has_voted' => 1], ['user_id' => $userId]);
		$this->db->commit();
	}

	/**
	 * Inserts a new poll.
	 *
	 * @param Poll $poll
	 * @return int the id of the created poll
	 * @throws Exception
	 */
	public function insertPoll(Poll $poll): int
	{
		return $this->db->insert('fs_poll', [
			'region_id' => $poll->regionId,
			'scope' => $poll->scope,
			'name' => $poll->name,
			'description' => $poll->description,
			'type' => $poll->type,
			'start' => $this->db->now(),
			'end' => $poll->endDate
		]);
	}
}
