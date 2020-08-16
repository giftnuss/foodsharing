<?php

namespace Foodsharing\Modules\Voting;

use Exception;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingScope;
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
		// assign option indices
		$index = 0;
		foreach ($poll->options as $option) {
			$option->optionIndex = $index++;
		}

		// create poll
		$userIds = $this->listUserIds($poll->regionId, $poll->scope);
		$poll->id = $this->votingGateway->insertPoll($poll, $userIds);
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
}
