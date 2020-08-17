<?php

namespace Foodsharing\Modules\Voting;

use DateTime;
use Exception;
use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Voting\DTO\Poll;
use Foodsharing\Modules\Voting\DTO\PollOption;

class VotingGateway extends BaseGateway
{
	/**
	 * Returns the detailed data of a poll.
	 *
	 * @param int $pollId a valid id of a poll
	 * @param bool $includeResults whether the counted votes should be included
	 *
	 * @return Poll the poll object
	 *
	 * @throws Exception if the poll with the given id does not exist
	 */
	public function getPoll(int $pollId, bool $includeResults): Poll
	{
		$data = $this->db->fetchByCriteria('fs_poll',
			['region_id', 'scope', 'name', 'description', 'type', 'start', 'end', 'author'],
			['id' => $pollId]
		);
		if (empty($data)) {
			throw new Exception('poll does not exist');
		}

		$options = $this->getOptions($pollId, $includeResults);

		return Poll::create($pollId, $data['name'], $data['description'],
			new DateTime($data['start']), new DateTime($data['end']),
			$data['region_id'], $data['scope'], $data['type'], $data['author'], $options);
	}

	/**
	 * Returns all options of a poll without the vote counts. If the poll does not exist or does not have any
	 * options an empty array is returned.
	 *
	 * @param int $pollId a valid id of a poll
	 * @param bool $includeResults whether the counted votes should be included
	 *
	 * @return array associative array that maps the option indices to {@link PollOption} objects
	 */
	public function getOptions(int $pollId, bool $includeResults): array
	{
		$columns = ['option', 'option_text'];
		if ($includeResults) {
			$columns = array_merge($columns, ['upvotes', 'neutralvotes', 'downvotes']);
		}
		try {
			$data = $this->db->fetchAllByCriteria('fs_poll_has_options', $columns, ['poll_id' => $pollId]);
		} catch (Exception $e) {
			$data = [];
		}

		$result = [];
		foreach ($data as $d) {
			$result[$d['option']] = $includeResults
				? PollOption::create($pollId, $d['option'], $d['option_text'], $d['upvotes'], $d['neutralvotes'], $d['downvotes'])
				: PollOption::create($pollId, $d['option'], $d['option_text']);
		}

		return $result;
	}

	/**
	 * Returns all options of a poll with the vote counts. If the poll does not exist or does not have any
	 * options an empty array is returned.
	 *
	 * @param int $pollId a valid id of a poll
	 *
	 * @return array multiple {@link PollOption} objects
	 */
	public function getResults(int $pollId): array
	{
		try {
			$data = $this->db->fetchAllByCriteria('fs_poll_has_options',
				['option', 'option_text', 'upvotes', 'neutralvotes', 'downvotes'],
				['poll_id' => $pollId]
			);
		} catch (Exception $e) {
			$data = [];
		}

		$result = [];
		foreach ($data as $x) {
			$result[$x['option']] = PollOption::create($pollId, $x['option'], $x['option_text'], $x['upvotes'], $x['neutralvotes'], $x['downvotes']);
		}

		return $result;
	}

	/**
	 * Returns all polls in a region or working group. If the region does not exists an empty array
	 * is returned.
	 *
	 * @param int $regionId a valid ID of a group or region
	 *
	 * @return array multiple {@link Poll} objects
	 */
	public function listPolls(int $regionId): array
	{
		$data = $this->db->fetchAllByCriteria('fs_poll',
			['id', 'region_id', 'scope', 'name', 'description', 'type', 'start', 'end', 'author'],
			['region_id' => $regionId]
		);

		$polls = [];
		foreach ($data as $d) {
			$options = $this->getOptions($d['id'], false);
			$polls[] = Poll::create($d['id'], $d['name'], $d['description'],
				new DateTime($d['start']), new DateTime($d['end']),
				$d['region_id'], $d['scope'], $d['type'], $d['author'], $options);
		}

		return $polls;
	}

	/**
	 * Returns whether a user is allowed to vote in a specific poll and has not voted yet.
	 *
	 * @param int $pollId a valid id of a poll
	 * @param int $userId a valid user id
	 *
	 * @return bool whether the user may vote in that poll
	 *
	 * @throws Exception
	 */
	public function mayUserVote(int $pollId, int $userId): bool
	{
		return $this->db->exists('fs_foodsaver_has_poll', [
			'poll_id' => $pollId,
			'foodsaver_id' => $userId,
			'has_voted' => 0
		]);
	}

	/**
	 * Updates the vote counters of a poll and updates that the user has voted.
	 *
	 * @param int $pollId a valid id of a poll
	 * @param int $userId a valid user id
	 * @param array $options a map from option index to the vote value (+1, -1, 0)
	 *
	 * @throws Exception if the poll does not exist
	 */
	public function vote(int $pollId, int $userId, array $options): void
	{
		$columns = ['downvotes', 'neutralvotes', 'upvotes'];
		$this->db->execute('LOCK TABLES fs_foodsaver_has_poll WRITE, fs_poll_has_option WRITE');
		$this->db->beginTransaction();

		foreach ($options as $index => $vote) {
			// increment one of the columns depending on the vote for this option
			$column = $columns[$vote + 1];
			$this->db->execute('
				UPDATE fs_poll_has_options
				SET ' . $column . ' = ' . $column . '+1
				WHERE poll_id = :pollId
				AND option = :option',
				[
					':pollId' => $pollId,
					':option' => $index
				]);
		}

		$this->db->update('fs_foodsaver_has_poll', ['has_voted' => 1], ['foodsaver_id' => $userId]);
		$this->db->commit();
		$this->db->execute('UNLOCK TABLES');
	}

	/**
	 * Inserts a new poll.
	 *
	 * @param Poll $poll a valid poll object
	 * @param array $userIds the ids of all users that will be allowed to vote
	 *
	 * @return int the id of the created poll
	 *
	 * @throws Exception
	 */
	public function insertPoll(Poll $poll, array $userIds): int
	{
		// insert the poll
		$pollId = $this->db->insert('fs_poll', [
			'region_id' => $poll->regionId,
			'name' => $poll->name,
			'description' => $poll->description,
			'scope' => $poll->scope,
			'type' => $poll->type,
			'start' => $poll->startDate->format('Y-m-d H:i:s'),
			'end' => $poll->endDate->format('Y-m-d H:i:s'),
			'author' => $poll->authorId
		]);

		// insert all options
		foreach ($poll->options as $index => $option) {
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
			]);
		}

		return $pollId;
	}

	/**
	 * Deletes a poll and all its options.
	 *
	 * @param int $pollId a valid poll ID
	 *
	 * @throws Exception
	 */
	public function deletePoll(int $pollId): void
	{
		$this->db->delete('fs_poll', ['id' => $pollId]);
	}

	public function listActiveRegionMemberIds(int $regionId, int $minRole, bool $onlyVerified = true): array
	{
		$verifiedCondition = $onlyVerified ? 'AND fs.verified = 1' : '';

		return $this->db->fetchAll('
			SELECT id
			FROM fs_foodsaver fs
			INNER JOIN fs_foodsaver_has_bezirk hb
			ON fs.id = hb.foodsaver_id
			WHERE hb.bezirk_id = :regionId
			AND hb.active = 1
			AND fs.rolle > :role
			' . $verifiedCondition, [
			':regionId' => $regionId,
			':role' => $minRole
		]);
	}
}
