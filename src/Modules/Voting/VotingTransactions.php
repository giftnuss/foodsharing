<?php

namespace Foodsharing\Modules\Voting;

use Exception;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingScope;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingType;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Voting\DTO\Poll;
use Foodsharing\Permissions\VotingPermissions;

class VotingTransactions
{
	private VotingGateway $votingGateway;
	private FoodsaverGateway $foodsaverGateway;
	private StoreGateway $storeGateway;
	private RegionGateway $regionGateway;
	private BellGateway $bellGateway;
	private VotingPermissions $votingPermissions;
	private Session $session;

	public function __construct(
		VotingGateway $votingGateway,
		FoodsaverGateway $foodsaverGateway,
		StoreGateway $storeGateway,
		RegionGateway $regionGateway,
		BellGateway $bellGateway,
		VotingPermissions $votingPermissions,
		Session $session
	) {
		$this->votingGateway = $votingGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->storeGateway = $storeGateway;
		$this->regionGateway = $regionGateway;
		$this->bellGateway = $bellGateway;
		$this->votingPermissions = $votingPermissions;
		$this->session = $session;
	}

	/**
	 * Creates a new poll for a region or work group and invites members based on the poll's scope. This also
	 * assigns a valid ID to the poll object and all options.
	 *
	 * @param Poll $poll a valid poll object
	 * @param bool $notifyVoters whether the voters should be notified by a bell
	 *
	 * @throws Exception
	 */
	public function createPoll(Poll &$poll, bool $notifyVoters): void
	{
		// assign valid indices and values to the options
		$this->updateOptionValues($poll);

		// create poll
		$voterIds = $this->listUserIds($poll->regionId, $poll->scope);
		$poll->id = $this->votingGateway->insertPoll($poll, $voterIds);

		// assign poll ID to the options
		foreach ($poll->options as $option) {
			$option->pollId = $poll->id;
		}

		if ($notifyVoters) {
			$this->notifyUsers($poll, $voterIds);
		}
	}

	/**
	 * Assigns valid indices and values to all options in the poll. This need to be done before a poll is
	 * stored in the gateway.
	 */
	private function updateOptionValues(Poll &$poll): void
	{
		// assign valid indices and values to the options
		$possibleValues = $this->getPossibleValues($poll->type);
		$possibleValues = array_combine($possibleValues, array_fill(0, sizeof($possibleValues), 0));
		$mappedOptions = [];
		$index = 0;
		foreach ($poll->options as $option) {
			$option->optionIndex = $index;
			$option->values = $possibleValues;
			$mappedOptions[$index++] = $option;
		}
		$poll->options = $mappedOptions;
	}

	/**
	 * Returns a list of possible option values for a poll's type.
	 *
	 * @param int $pollType type of a poll, see {@see VotingType}
	 *
	 * @return int[] list of possible values
	 *
	 * @throws Exception if the type is not valid
	 */
	private function getPossibleValues(int $pollType)
	{
		switch ($pollType) {
			case VotingType::SELECT_ONE_CHOICE:
			case VotingType::SELECT_MULTIPLE:
				return [1];
			case VotingType::THUMB_VOTING:
				return [1, 0, -1];
			case VotingType::SCORE_VOTING:
				return [3, 2, 1, 0, -1, -2, -3];
			default:
				throw new Exception('invalid poll type');
		}
	}

	/**
	 * Lists the ids of all users from a region that will be allowed to vote based on a poll's scope.
	 *
	 * @param int $regionId id of the poll's region
	 * @param int $scope a poll's scope, see {@link VotingScope}
	 *
	 * @throws Exception if the scope is not a valid type
	 *
	 * @return array a list of user ids
	 */
	private function listUserIds(int $regionId, int $scope): array
	{
		switch ($scope) {
			case VotingScope::FOODSAVERS:
				$users = $this->votingGateway->listActiveRegionMemberIds($regionId, Role::FOODSAVER, false);
				break;
			case VotingScope::VERIFIED_FOODSAVERS:
				$users = $this->votingGateway->listActiveRegionMemberIds($regionId, Role::FOODSAVER, true);
				break;
			case VotingScope::VERIFIED_FOODSAVERS_HOME_DISTRICT:
				$users = $this->votingGateway->listActiveRegionMemberIds($regionId, Role::FOODSAVER, true, true);
				break;
			case VotingScope::STORE_MANAGERS:
				$users = $this->storeGateway->getStoreManagersOf($regionId);
				break;
			case VotingScope::AMBASSADORS:
				$users = $this->votingGateway->getAmbassadorsIDsOfSubregions($regionId);
				break;
			default:
				throw new Exception('invalid voting scope');
		}

		return $users;
	}

	/**
	 * Notifies all users in the list (except the author of the poll) via a bell that a new poll
	 * has been created.
	 */
	private function notifyUsers(Poll $poll, array $userIds): void
	{
		$region = $this->regionGateway->getRegion($poll->regionId);

		$usersWithoutPostAuthor = array_diff($userIds, [$poll->authorId]);
		$bellData = Bell::create(
			'poll_new_title',
			'poll_new',
			'fas fa-poll-h',
			['href' => '/?page=poll&id=' . $poll->id],
			['title' => $poll->name, 'region' => $region['name']],
			'new-poll-' . $poll->id
		);
		$this->bellGateway->addBell($usersWithoutPostAuthor, $bellData);
	}

	/**
	 * Cancels a poll by movin its end date into the past and removes all bell notifications for it.
	 */
	public function cancelPoll(int $pollId): void
	{
		$this->votingGateway->cancelPoll($pollId, $this->session->id());
		$this->bellGateway->delBellsByIdentifier('new-poll-' . $pollId);
	}

	/**
	 * Checks whether the vote is valid for the poll's type and options.
	 *
	 * @param Poll $poll an ongoing poll
	 * @param array $values a map from option index to the voted value for that option
	 *
	 * @return bool if the vote is valid
	 */
	private function isValidVote(Poll $poll, array $values): bool
	{
		// make sure the option indices fit the poll's options and all voted value are valid
		foreach ($values as $index => $value) {
			if (!isset($poll->options[$index]) || !in_array($value, array_keys($poll->options[$index]->values))) {
				return false;
			}
		}

		// check contraints given by voting type
		switch ($poll->type) {
			case VotingType::SELECT_ONE_CHOICE:
				// only one option possible
				if (sizeof($values) !== 1) {
					return false;
				}
				break;
			case VotingType::SELECT_MULTIPLE:
				// multiple options possible, but at most as many as options in the poll
				if (sizeof($values) > sizeof($poll->options)) {
					return false;
				}
				break;
			case VotingType::THUMB_VOTING:
			case VotingType::SCORE_VOTING:
				// each option must have a value
				if (sizeof($poll->options) != sizeof($values)) {
					return false;
				}
				break;
		}

		return true;
	}

	/**
	 * Casts a vote in a poll. Returns whether the selection was valid and the vote was accepted.
	 *
	 * @param Poll $poll an ongoing poll
	 * @param array $values a map from option index to the voted value for that option
	 *
	 * @return bool if the vote is valid
	 */
	public function vote(Poll $poll, array $values): bool
	{
		if (!$this->isValidVote($poll, $values)) {
			return false;
		}

		$this->votingGateway->vote($poll->id, $this->session->id(), $values);

		// remove the 'new poll' bell for this user
		try {
			$bellId = $this->bellGateway->getOneByIdentifier('new-poll-' . $poll->id);
			$this->bellGateway->delBellForFoodsaver($bellId, $this->session->id());
		} catch (\Exception $e) {
			// in case the bell does not exist, do nothing
		}

		return true;
	}

	/**
	 * Helper function that fetches a poll from the database and removes all results and the number of
	 * current votes from a poll if the current user is not allowed to see them
	 * (see {@see VotingPermissions::maySeeResults}).
	 *
	 * @param int $pollId a valid id of a poll
	 * @param bool $includeResults whether the counted votes should be included
	 *
	 * @return Poll the poll object or null if this poll ID doesn't exist
	 */
	public function getPoll(int $pollId, bool $includeResults): ?Poll
	{
		$poll = $this->votingGateway->getPoll($pollId, $includeResults);

		if (!is_null($poll) && (!$includeResults || !$this->votingPermissions->maySeeResults($poll))) {
			$poll->votes = null;
			foreach ($poll->options as &$option) {
				$option->values = array_fill_keys(array_keys($option->values), -1);
			}
		}

		return $poll;
	}

	public function updatePoll(Poll $poll): void
	{
		// assign valid indices and values to the options
		$this->updateOptionValues($poll);

		// save poll
		$this->votingGateway->updatePoll($poll);
	}
}
