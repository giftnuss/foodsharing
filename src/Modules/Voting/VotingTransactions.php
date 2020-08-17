<?php

namespace Foodsharing\Modules\Voting;

use Exception;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingScope;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingType;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Voting\DTO\Poll;

class VotingTransactions
{
	private VotingGateway $votingGateway;
	private FoodsaverGateway $foodsaverGateway;
	private StoreGateway $storeGateway;
	private RegionGateway $regionGateway;
	private BellGateway $bellGateway;

	public function __construct(
		VotingGateway $votingGateway,
		FoodsaverGateway $foodsaverGateway,
		StoreGateway $storeGateway,
		RegionGateway $regionGateway,
		BellGateway $bellGateway)
	{
		$this->votingGateway = $votingGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->storeGateway = $storeGateway;
		$this->regionGateway = $regionGateway;
		$this->bellGateway = $bellGateway;
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
	public function createPollForRegion(Poll &$poll, bool $notifyVoters): void
	{
		// assign valid indices to the options
		$mappedOptions = [];
		$index = 0;
		foreach ($poll->options as $option) {
			$option->optionIndex = $index;
			$mappedOptions[$index++] = $option;
		}
		$poll->options = $mappedOptions;

		// create poll
		$userIds = $this->listUserIds($poll->regionId, $poll->scope);
		$poll->id = $this->votingGateway->insertPoll($poll, $userIds);

		// assign poll ID to the options
		foreach ($poll->options as $option) {
			$option->pollId = $poll->id;
		}

		if ($notifyVoters) {
			$this->notifyUsers($poll, $userIds);
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
			case VotingScope::ALL_USERS:
				$users = $this->votingGateway->listActiveRegionMemberIds($regionId, Role::FOODSHARER, false);
				break;
			case VotingScope::FOODSAVERS:
				$users = $this->votingGateway->listActiveRegionMemberIds($regionId, Role::FOODSAVER, false);
				break;
			case VotingScope::VERIFIED_FOODSAVERS:
				$users = $this->votingGateway->listActiveRegionMemberIds($regionId, Role::FOODSAVER);
				break;
			case VotingScope::STORE_MANAGERS:
				$users = $this->storeGateway->getStoreManagersOf($regionId);
				break;
			case VotingScope::AMBASSADORS:
				$users = array_map(function ($x) {
					return $x['id'];
				}, $this->foodsaverGateway->getAdminsOrAmbassadors($regionId));
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
			['href' => '/?page=bezirk&sub=polls&id=' . $poll->id],
			['title' => $poll->name, 'region' => $region['name']],
			'new-poll-' . $poll->id
		);
		$this->bellGateway->addBell($usersWithoutPostAuthor, $bellData);
	}

	/**
	 * Deletes a poll from the database and removes all bell notifications for it.
	 */
	public function deletePoll(int $pollId): void
	{
		$this->votingGateway->deletePoll($pollId);
		$this->bellGateway->delBellsByIdentifier('new-poll-' . $pollId);
	}

	/**
	 * Checks whether the vote is valid for the poll's type and options.
	 *
	 * @param Poll $poll an ongoing poll
	 * @param array $options a map from option index to the voted value for that option
	 *
	 * @return bool if the vote is valid
	 */
	public function isValidVote(Poll $poll, array $options): bool
	{
		// make sure the option indices fit the poll's options
		foreach ($options as $index => $value) {
			if ($index < 0 || $index >= sizeof($poll->options)) {
				return false;
			}
		}

		// check contraints given by voting type
		switch ($poll->type) {
			case VotingType::SELECT_ONE_CHOICE:
				// only one +1 option (upvote) possible
				if (sizeof($options) !== 1 || $options[0] !== 1) {
					return false;
				}
				break;
			case VotingType::SELECT_MULTIPLE:
				// multiple +1 options (upvotes) possible, but at most as many as options in the poll
				if (sizeof($poll->options) <= sizeof($options)) {
					return false;
				}
				break;
			case VotingType::SCORE_VOTING:
				// each option must have a value and all values must be +1, 0, or -1
				if (sizeof($poll->options) != sizeof($options)
					|| !$this->areArrayValuesValid(array_values($options), [-1, 0, 1])) {
					return false;
				}
				break;
		}

		return true;
	}

	/**
	 * Returns whether each value in the array x is one of the possible values.
	 */
	private function areArrayValuesValid(array $x, array $possibleValues): bool
	{
		foreach ($x as $value) {
			if (!in_array($value, $possibleValues)) {
				return false;
			}
		}

		return true;
	}
}
