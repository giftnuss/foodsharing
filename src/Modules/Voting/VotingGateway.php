<?php

namespace Foodsharing\Modules\Voting;

use Carbon\Carbon;
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
		// meta-data of option
		try {
			$data = $this->db->fetchAllByCriteria('fs_poll_has_options', ['option', 'option_text'], ['poll_id' => $pollId]);
		} catch (Exception $e) {
			$data = [];
		}

		// values and counted votes
		$result = [];
		foreach ($data as $d) {
			if ($includeResults) {
				$values = $this->db->fetchAllByCriteria('fs_poll_option_has_value', ['value', 'votes'], [
					'poll_id' => $pollId, 'option' => $d['option']]);
				$mappedValues = [];
				foreach ($values as $v) {
					$mappedValues[$v['value']] = $v['votes'];
				}
			} else {
				$values = $this->db->fetchAllValuesByCriteria('fs_poll_option_has_value', 'value', [
					'poll_id' => $pollId, 'option' => $d['option']]);
				$mappedValues = array_combine($values, array_fill(0, sizeof($values), -1));
			}
			$result[$d['option']] = PollOption::create($pollId, $d['option'], $d['option_text'], $mappedValues);
		}

		return $result;
	}

	/**
	 * Returns all polls in a region or working group. If the region does not exist an empty array
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
	 * Returns whether a user has already voted in a specific poll.
	 *
	 * @param int $pollId a valid id of a poll
	 * @param int $userId a valid user id
	 *
	 * @return bool whether the user has voted in that poll
	 *
	 * @throws Exception
	 */
	public function hasUserVoted(int $pollId, int $userId): bool
	{
		return $this->db->exists('fs_foodsaver_has_voted', [
			'poll_id' => $pollId,
			'foodsaver_id' => $userId
		]);
	}

	/**
	 * Updates the vote counters of a poll and updates that the user has voted.
	 *
	 * @param int $pollId a valid id of a poll
	 * @param int $userId a valid user id
	 * @param array $options a map from option index to the vote value
	 *
	 * @throws Exception if the poll does not exist or if one of the chosen values for an option is invalid
	 */
	public function vote(int $pollId, int $userId, array $options): void
	{
		$this->db->execute('LOCK TABLES fs_foodsaver_has_voted WRITE, fs_poll_has_option WRITE');
		$this->db->beginTransaction();

		foreach ($options as $option => $voteValue) {
			// increment one of the columns depending on the vote for this option
			$this->db->execute('
				UPDATE fs_poll_option_has_value
				SET votes = votes+1
				WHERE poll_id = :pollId
				AND option = :option',
				[
					':pollId' => $pollId,
					':option' => $option
				]);
		}

		$this->db->insert('fs_foodsaver_has_voted', [
			'poll_id' => $pollId,
			'foodsaver_id' => $userId,
			'time' => $this->db->now()
		]);
		$this->db->commit();
		$this->db->execute('UNLOCK TABLES');
	}

	/**
	 * Inserts a new poll.
	 *
	 * @param Poll $poll a valid poll object
	 *
	 * @return int the id of the created poll
	 *
	 * @throws Exception
	 */
	public function insertPoll(Poll $poll): int
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
				'option_text' => $option->text
			]);

			// insert all values for this option
			foreach (array_keys($option->values) as $value) {
				$this->db->insert('fs_poll_option_has_value', [
					'poll_id' => $pollId,
					'option' => $option->optionIndex,
					'value' => $value,
					'votes' => 0
				]);
			}
		}

		return $pollId;
	}

	/**
	 * Sets a poll's end date to the past and logs who cancelled it.
	 *
	 * @param int $pollId a valid poll ID
	 * @param int $userId ID of the user who cancelled the poll
	 *
	 * @throws Exception
	 */
	public function cancelPoll(int $pollId, int $userId): void
	{
		$this->db->update('fs_poll', [
			'end' => $this->db->date(Carbon::now()->subMinute()),
			'cancelled_by' => $userId
		], ['id' => $pollId]);
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
