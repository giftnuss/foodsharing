<?php

namespace Foodsharing\RestApi;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Buddy\BuddyGateway;
use Foodsharing\Modules\Buddy\BuddyTransactions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BuddyRestController extends AbstractFOSRestController
{
	private BuddyTransactions $buddyTransactions;
	private BuddyGateway $buddyGateway;
	private Session $session;

	public function __construct(
		BuddyTransactions $buddyTransactions,
		BuddyGateway $buddyGateway,
		Session $session
	) {
		$this->buddyTransactions = $buddyTransactions;
		$this->buddyGateway = $buddyGateway;
		$this->session = $session;
	}

	/**
	 * Sends a buddy request to a user.
	 *
	 * @OA\Parameter(name="userId", in="path", @OA\Schema(type="integer"), description="which user to send the request to")
	 * @OA\Response(response="200", description="Success.", @OA\Schema(type="object",
	 *     @OA\Property(property="isBuddy", type="integer", description="whether the other user is now this user's buddy")
	 * ))
	 * @OA\Response(response="400", description="Already buddy with that user.")
	 * @OA\Response(response="403", description="Insufficient permissions to send the request.")
	 * @OA\Tag(name="tag")
	 *
	 * @Rest\Put("buddy/{userId}", requirements={"userId" = "\d+"})
	 */
	public function sendRequestAction(int $userId): Response
	{
		if (!$this->session->id()) {
			throw new HttpException(403);
		}

		if (in_array($userId, $this->buddyGateway->listBuddyIds($this->session->id()))) {
			throw new HttpException(400);
		}

		$accepting = $this->buddyGateway->buddyRequestedMe($userId, $this->session->id());
		if ($accepting) {
			$this->buddyTransactions->acceptBuddyRequest($userId);
		} else {
			$this->buddyTransactions->sendBuddyRequest($userId);
		}

		return $this->handleView($this->view(['isBuddy' => $accepting], 200));
	}
}
