<?php

namespace Foodsharing\Modules\Voting;

use Exception;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Voting\VotingScope;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Store\StoreGateway;
use Foodsharing\Modules\Voting\DTO\Poll;
use Foodsharing\Services\NotificationService;

class VotingTransactions
{
	private $votingGateway;
	private $foodsaverGateway;
	private $storeGateway;
	private $notificationService;

	public function __construct(
		VotingGateway $votingGateway,
		FoodsaverGateway $foodsaverGateway,
		StoreGateway $storeGateway,
		NotificationService $notificationService)
	{
		$this->votingGateway = $votingGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->storeGateway = $storeGateway;
		$this->notificationService = $notificationService;
	}

	/**
	 * Creates a new poll for a region or work group and invites members based on the poll's scope.
	 *
	 * @throws Exception
	 */
	public function createPollForRegion(Poll $poll, array $options, int $regionId, bool $notifyUsers)
	{
		$userIds = $this->listUserIds($regionId, $poll->scope);
		$this->votingGateway->insertPoll($poll, $options, $userIds);

		if ($notifyUsers) {
			$this->notificationService->newPoll($poll, $userIds);
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
}
