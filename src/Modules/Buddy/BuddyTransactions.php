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
	) {
		$this->buddyGateway = $buddyGateway;
		$this->bellGateway = $bellGateway;
		$this->session = $session;
		$this->imageHelper = $imageHelper;
	}

	/**
	 * Updates the buddy status and deletes open bell notifications.
	 *
	 * @param int $userId ID of another user
	 */
	public function acceptBuddyRequest(int $userId): void
	{
		$this->buddyGateway->confirmBuddy($userId, $this->session->id());

		$this->bellGateway->delBellsByIdentifier('buddy-' . $this->session->id() . '-' . $userId);
		$this->bellGateway->delBellsByIdentifier('buddy-' . $userId . '-' . $this->session->id());

		$buddyIds = [];
		if ($b = $this->session->get('buddy-ids')) {
			$buddyIds = $b;
		}

		$buddyIds[$userId] = $userId;
		$this->session->set('buddy-ids', $buddyIds);
	}

	/**
	 * Sends a buddy request and creates a bell notification.
	 *
	 * @param int $userId ID of another user
	 */
	public function sendBuddyRequest(int $userId): void
	{
		$this->buddyGateway->buddyRequest($userId, $this->session->id());
		$this->bellGateway->addBell($userId, Bell::create(
			'buddy_request_title',
			'buddy_request',
			$this->imageHelper->img($this->session->user('photo')),
			['href' => '/profile/' . (int)$this->session->id() . ''],
			['name' => $this->session->user('name')],
			'buddy-' . $this->session->id() . '-' . $userId
		));
	}
}
