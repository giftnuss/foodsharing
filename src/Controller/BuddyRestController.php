<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Buddy\BuddyTransactions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BuddyRestController extends AbstractFOSRestController
{
	private BuddyTransactions $buddyTransactions;
	private Session $session;

	public function __construct(BuddyTransactions $buddyTransactions, Session $session)
	{
		$this->buddyTransactions = $buddyTransactions;
		$this->session = $session;
	}

	/**
	 * Sends a buddy request to a user.
	 *
	 * @Rest\Put("buddy/{userId}", requirements={"userId" = "\d+"})
	 */
	public function sendRequestAction(int $userId)
	{
		if (!$this->session->id()) {
			throw new HttpException(403);
		}

		$isBuddy = $this->buddyTransactions->processBuddyRequest($userId);

		return $this->handleView($this->view(['isBuddy' => $isBuddy], 200));
	}
}
