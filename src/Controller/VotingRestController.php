<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Voting\VotingGateway;
use Foodsharing\Permissions\VotingPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VotingRestController extends AbstractFOSRestController
{
	private $session;
	private $votingGateway;
	private $votingPermissions;

	public function __construct(Session $session, VotingGateway $votingGateway, VotingPermissions $votingPermissions)
	{
		$this->session = $session;
		$this->votingGateway = $votingGateway;
		$this->votingPermissions = $votingPermissions;
	}

	/**
	 * Vote in a poll.
	 *
	 * @Rest\Put("polls/{pollId}/vote", requirements={"pollId" = "\d+"})
	 */
	public function voteAction(int $pollId)
	{
		if ($this->votingPermissions->mayVote($pollId)) {
			throw new HttpException(403);
		}

		$options = []; //TODO
		$this->votingGateway->vote($pollId, $this->session->id(), $options);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Creates a new poll.
	 *
	 * @Rest\Post("polls")
	 */
	public function createPollAction(ParamFetcher $paramFetcher)
	{
		//TODO
	}
}
