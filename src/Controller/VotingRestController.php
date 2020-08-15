<?php

namespace Foodsharing\Controller;

use Exception;
use Foodsharing\Lib\Session;
use Foodsharing\Modules\Voting\VotingGateway;
use Foodsharing\Modules\Voting\VotingTransactions;
use Foodsharing\Permissions\VotingPermissions;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VotingRestController extends AbstractFOSRestController
{
	private Session $session;
	private VotingGateway $votingGateway;
	private VotingPermissions $votingPermissions;
	private VotingTransactions $votingTransactions;

	public function __construct(
		Session $session,
		VotingGateway $votingGateway,
		VotingPermissions $votingPermissions,
		VotingTransactions $votingTransactions)
	{
		$this->session = $session;
		$this->votingGateway = $votingGateway;
		$this->votingPermissions = $votingPermissions;
		$this->votingTransactions = $votingTransactions;
	}

	/**
	 * Returns the details of a poll.
	 *
	 * @SWG\Parameter(name="pollId", in="path", type="integer", description="which poll to return")
	 * @SWG\Response(response="200", description="Success")
	 * @SWG\Response(response="403", description="Insufficient permissions to view that poll.")
	 * @SWG\Response(response="404", description="Poll does not exist.")
	 * @SWG\Tag(name="polls")
	 *
	 * @Rest\Get("polls/{pollId}", requirements={"pollId" = "\d+"})
	 */
	public function getPoll(int $pollId): Response
	{
		try {
			$poll = $this->votingGateway->getPoll($pollId, $this->votingPermissions->maySeeResults($pollId));
		} catch (Exception $e) {
			throw new HttpException(404);
		}

		if (!$this->votingPermissions->maySeePoll($pollId, $poll->regionId)) {
			throw new HttpException(403);
		}

		return $this->handleView($this->view($poll, 200));
	}

	/**
	 * Lists all polls in a region or working group.
	 *
	 * @SWG\Parameter(name="regionId", in="path", type="integer", description="which region to list polls for")
	 * @SWG\Response(response="200", description="Success")
	 * @SWG\Response(response="403", description="Insufficient permissions to list polls in that group.")
	 * @SWG\Tag(name="polls")
	 *
	 * @Rest\Get("polls/group/{groupId}", requirements={"groupId" = "\d+"})
	 */
	public function listPolls(int $groupId): Response
	{
		if (!$this->votingPermissions->mayListPolls($groupId)) {
			throw new HttpException(403);
		}

		$polls = $this->votingGateway->listPolls($groupId);

		return $this->handleView($this->view($polls, 200));
	}

	/**
	 * Vote in a poll.
	 *
	 * @SWG\Parameter(name="pollId", in="path", type="integer", description="in which poll to vote")
	 * @SWG\Response(response="200", description="Success")
	 * @SWG\Response(response="403", description="Insufficient permissions to vote in that polls.")
	 * @SWG\Tag(name="polls")
	 *
	 * @Rest\Put("polls/{pollId}/vote", requirements={"pollId" = "\d+"})
	 */
	public function voteAction(int $pollId): Response
	{
		if (!$this->votingPermissions->mayVote($pollId)) {
			throw new HttpException(403);
		}

		$options = []; //TODO
		$this->votingGateway->vote($pollId, $this->session->id(), $options);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * Creates a new poll.
	 *
	 * @SWG\Response(response="200", description="Success")
	 * @SWG\Response(response="403", description="Insufficient permissions to create a poll in that region.")
	 * @SWG\Tag(name="polls")
	 *
	 * @Rest\Post("polls/{groupId}", requirements={"groupId" = "\d+"})
	 */
	public function createPollAction(int $groupId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->votingPermissions->mayCreatePoll($groupId)) {
			throw new HttpException(403);
		}

		//TODO
		return $this->handleView($this->view([], 200));
	}

	/**
	 * Deletes a poll.
	 *
	 * @SWG\Response(response="200", description="Success")
	 * @SWG\Response(response="403", description="Insufficient permissions to delete that poll.")
	 * @SWG\Response(response="404", description="Poll does not exist.")
	 * @SWG\Tag(name="polls")
	 *
	 * @Rest\Delete("polls/{pollId}", requirements={"pollId" = "\d+"})
	 */
	public function deletePollAction(int $pollId): Response
	{
		try {
			$poll = $this->votingGateway->getPoll($pollId, false);
		} catch (Exception $e) {
			throw new HttpException(404);
		}

		if (!$this->votingPermissions->mayDeletePoll($pollId)) {
			throw new HttpException(403);
		}

		return $this->handleView($this->view($poll, 200));
	}
}
