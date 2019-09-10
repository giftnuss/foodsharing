<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Services\MessageService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MessageRestController extends AbstractFOSRestController
{
	private $foodsaverGateway;
	private $messageGateway;
	private $messageService;
	private $session;

	public function __construct(FoodsaverGateway $foodsaverGateway, MessageGateway $messageGateway, MessageService $messageService, Session $session)
	{
		$this->foodsaverGateway = $foodsaverGateway;
		$this->messageGateway = $messageGateway;
		$this->messageService = $messageService;
		$this->session = $session;
	}

	/**
	 * @Rest\Get("conversations/{conversationId}/messages", requirements={"conversationId" = "\d+"})
	 * @Rest\QueryParam(name="olderThanId", requirements="\d+", nullable=true, default=null, description="ID of oldest already known message")
	 * @Rest\QueryParam(name="limit", requirements="\d+", default="20", description="Number of messages to return")
	 *
	 * @param int $conversationId
	 * @param ParamFetcher $paramFetcher
	 *
	 * @return Response
	 */
	public function getConversationMessagesAction(int $conversationId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->may() || !$this->messageGateway->mayConversation($this->session->id(), $conversationId)) {
			throw new HttpException(401);
		}

		$limit = (int)$paramFetcher->get('limit');
		$olderThanID = $paramFetcher->get('olderThanId');
		$olderThanID = $olderThanID ? (int)$olderThanID : null;

		$messages = $this->messageGateway->getConversationMessages($conversationId, $limit, $olderThanID);
		$profileIDs = [];
		array_walk($messages, function ($v, $k) use (&$profileIDs) {
			$profileIDs[] = $v->authorId;
		});
		$profileIDs = array_unique($profileIDs);
		$profiles = $this->foodsaverGateway->getProfileForUsers($profileIDs);

		return $this->handleView($this->view(['messages' => $messages, 'profiles' => $profiles], 200));
	}

	/**
	 * @Rest\Get("conversations/{conversationId}", requirements={"conversationId" = "\d+"})
	 * @Rest\QueryParam(name="messagesLimit", requirements="\d+", default="20", description="How many messages to return.")
	 *
	 * @param int $conversationId
	 * @param ParamFetcher $paramFetcher
	 *
	 * @return Response
	 */
	public function getConversationAction(int $conversationId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->may() || !$this->messageGateway->mayConversation($this->session->id(), $conversationId)) {
			throw new HttpException(401);
		}

		$messagesLimit = $paramFetcher->get('messagesLimit');

		$conversationData = $this->getConversationData($conversationId, $messagesLimit);

		$view = $this->view($conversationData, 200);

		return $this->handleView($view);
	}

	private function getConversationData(int $conversationId, int $messagesLimit): array
	{
		$members = $this->messageGateway->getMembersForConversations([$conversationId])[$conversationId];
		$messages = $this->messageGateway->getConversationMessages($conversationId, $messagesLimit);
		$name = $this->messageGateway->getConversationName($conversationId);
		$this->messageGateway->markAsRead($conversationId, $this->session->id());

		$profileIDs = [];
		array_walk($messages, function ($v, $k) use (&$profileIDs) {
			$profileIDs[] = $v->authorId;
		});
		$profileIDs = array_merge($profileIDs, $members);
		$profileIDs = array_unique($profileIDs);
		$profiles = $this->foodsaverGateway->getProfileForUsers($profileIDs);

		return [
			'id' => $conversationId,
			'name' => $name,
			'members' => $members,
			'messages' => $messages,
			'profiles' => $profiles,
		];
	}

	/**
	 * @Rest\Post("conversations")
	 * @Rest\RequestParam(name="members", map=true, requirements="\d+", description="Userids of people to include in the conversation.")
	 *
	 * @param ParamFetcher $paramFetcher
	 *
	 * @return Response
	 */
	public function createConversationAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		$members = $paramFetcher->get('members');
		$members[] = $this->session->id();
		array_unique($members);

		$conversationId = $this->messageGateway->getOrCreateConversation($members);

		$conversationData = $this->getConversationData($conversationId, 20);

		return $this->handleView($this->view($conversationData, 200));
	}

	/**
	 * @Rest\Get("conversations")
	 * @Rest\QueryParam(name="limit", requirements="\d+", default="20", description="How many conversations to return.")
	 * @Rest\QueryParam(name="offset", requirements="\d+", default="0", description="Offset returned conversations.")
	 *
	 * @param ParamFetcher $paramFetcher
	 *
	 * @return Response
	 */
	public function getConversationsAction(ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		$limit = $paramFetcher->get('limit');
		$offset = $paramFetcher->get('offset');

		$conversations = $this->messageGateway->listConversationsForUser(
			$this->session->id(),
			$limit,
			$offset
		);

		return $this->handleView($this->view([
			'conversations' => array_values($conversations)
		], 200));
	}

	/**
	 * @Rest\Post("conversations/{conversationId}", requirements={"conversationId" = "\d+"})
	 * @Rest\RequestParam(name="body", nullable=false)
	 *
	 * @param int $conversationId
	 * @param ParamFetcher $paramFetcher
	 *
	 * @return Response
	 */
	public function sendMessageAction(int $conversationId, ParamFetcher $paramFetcher): Response
	{
		if (!$this->session->may() || !$this->messageGateway->mayConversation($this->session->id(), $conversationId)) {
			throw new HttpException(401);
		}
		$body = $paramFetcher->get('body');
		$this->messageService->sendMessage($conversationId, $this->session->id(), $body);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * @Rest\Patch("conversations/{conversationId}", requirements={"conversationId" = "\d+"})
	 * @Rest\RequestParam(name="name", nullable=true, default=null)
	 *
	 * @param int $conversationId
	 * @param ParamFetcher $paramFetcher
	 *
	 * @return Response
	 */
	public function patchConversationAction(int $conversationId, ParamFetcher $paramFetcher): Response
	{
		if ($this->messageGateway->isConversationLocked($conversationId) || !$this->session->may(
			) || !$this->messageGateway->mayConversation($this->session->id(), $conversationId)) {
			throw new HttpException(401);
		}

		if ($name = $paramFetcher->get('name')) {
			/* a name needs to have a non-zero length */
			$this->messageGateway->renameConversation($conversationId, $name);
		}

		return $this->handleView($this->view([], 200));
	}

	/**
	 * @Rest\Delete("conversations/{conversationId}/members/{userId}", requirements={"conversationId" = "\d+", "userId" = "\d+"})
	 *
	 * @param int $conversationId
	 * @param int $userId
	 *
	 * @return Response
	 */
	public function removeMemberFromConversationAction(int $conversationId, int $userId): Response
	{
		if (!$this->session->may() || $userId !== $this->session->id()) {
			/* only allow users to remove themselves from conversations */
			throw new HttpException(403);
		}
		if (!$this->messageService->deleteUserFromConversation($conversationId, $userId)) {
			throw new HttpException(400);
		}

		return $this->handleView($this->view([], 200));
	}

	/**
	 * @Rest\Post("user/{userId}/conversation")
	 *
	 * @param int $userId
	 *
	 * @return Response
	 */
	public function getUserConversationAction(int $userId): Response
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		$conversationId = $this->messageGateway->getOrCreateConversation([$this->session->id(), $userId]);

		return $this->handleView($this->view(['id' => $conversationId], 200));
	}
}
