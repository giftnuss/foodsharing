<?php

namespace Foodsharing\Modules\Buddy;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Utility\ImageHelper;

class BuddyTransactions
{
	private BuddyGateway $buddyGateway;
	private BellGateway $bellGateway;
	private Session $session;
	private ImageHelper $imageHelper;

	public function __construct(
		BuddyGateway $buddyGateway,
		BellGateway $bellGateway,
		Session $session,
		ImageHelper $imageHelper
	)
	{
		$this->buddyGateway = $buddyGateway;
		$this->bellGateway = $bellGateway;
		$this->session = $session;
		$this->imageHelper = $imageHelper;
	}

	/**
	 * Updates the buddy request status in the database, creates a bell notification, and returns whether the
	 * users are now buddies.
	 *
	 * @param int $userId ID of another user
	 *
	 * @return bool whether this and the other user are now buddies
	 */
	public function processBuddyRequest(int $userId): bool
	{
		if ($this->buddyGateway->buddyRequestedMe($userId, $this->session->id())) {
			$this->buddyGateway->confirmBuddy($userId, $this->session->id());

			$this->bellGateway->delBellsByIdentifier('buddy-' . $this->session->id() . '-' . $userId);
			$this->bellGateway->delBellsByIdentifier('buddy-' . $userId . '-' . $this->session->id());

			$buddyIds = [];
			if ($b = $this->session->get('buddy-ids')) {
				$buddyIds = $b;
			}

			$buddyIds[$userId] = $userId;
			$this->session->set('buddy-ids', $buddyIds);

			return true;
		} else {
			$this->buddyGateway->buddyRequest($userId, $this->session->id());
			$this->bellGateway->addBell($userId, Bell::create(
				'buddy_request_title',
				'buddy_request',
				$this->imageHelper->img($this->session->user('photo')),
				['href' => '/profile/' . (int)$this->session->id() . ''],
				['name' => $this->session->user('name')],
				'buddy-' . $this->session->id() . '-' . $userId
			));

			return false;
		}
	}
}
