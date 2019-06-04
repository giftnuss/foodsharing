<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Services\MessageService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MessageRestController extends AbstractFOSRestController
{
	private $messageGateway;
	private $messageService;
	private $session;

	public function __construct(MessageGateway $messageGateway, MessageService $messageService, Session $session)
	{
		$this->messageGateway = $messageGateway;
		$this->messageService = $messageService;
		$this->session = $session;
	}

	/**
	 * @Rest\Get("conversations/{conversationId}/messages", requirements={"conversationId" = "\d+"})
	 * @Rest\QueryParam(name="olderThanId", requirements="\d+", description="ID of oldest already known message")
	 * @Rest\QueryParam(name="limit", requirements="\d+", default="20", description="Number of messages to return")
	 */
	public function getConversationMessagesAction(int $conversationId, ParamFetcher $paramFetcher)
	{
		if (!$this->session->may() || !$this->messageGateway->mayConversation($this->session->id(), $conversationId)) {
			throw new HttpException(401);
		}

		$limit = (int)$paramFetcher->get('limit');
		$olderThanID = (int)$paramFetcher->get('olderThanId');

		$messages = $this->messageGateway->getConversationMessages($conversationId, $limit, $olderThanID);

		return $this->handleView($this->view(['messages' => $messages], 200));
	}

	/**
	 * @Rest\Get("conversations/{conversationId}", requirements={"conversationId" = "\d+"}, defaults={"messagesNumber" = 20})
	 * @Rest\QueryParam(name="messagesLimit", requirements="\d+", default="20", description="How many messages to return.")
	 */
	public function getConversationAction(int $conversationId, ParamFetcher $paramFetcher)
	{
		if (!$this->session->may() || !$this->messageGateway->mayConversation($this->session->id(), $conversationId)) {
			throw new HttpException(401);
		}

		$messagesLimit = $paramFetcher->get('messagesLimit');

		$members = $this->messageGateway->listConversationMembersWithProfile($conversationId);
		$publicMemberInfo = function ($member) {
			return RestNormalization::normalizeFoodsaver($member);
		};
		$members = array_map($publicMemberInfo, $members);

		$messages = $this->messageGateway->getConversationMessages($conversationId, $messagesLimit);
		$name = $this->messageGateway->getConversationName($conversationId);
		$this->messageGateway->markAsRead($conversationId, $this->session->id());

		$conversationData = [
			'name' => $name,
			'members' => $members,
			'messages' => $messages,
		];

		$view = $this->view($conversationData, 200);

		return $this->handleView($view);
	}

	/**
	 * @Rest\Get("conversations")
	 * @Rest\QueryParam(name="limit", requirements="\d+", default="20", description="How many conversations to return.")
	 * @Rest\QueryParam(name="offset", requirements="\d+", default="0", description="Offset returned conversations.")
	 */
	public function getConversationsAction(ParamFetcher $paramFetcher)
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		$limit = $paramFetcher->get('limit');
		$offset = $paramFetcher->get('offset');

		$conversations = $this->messageGateway->listConversationsForUserIncludeProfiles($this->session->id(), $limit, $offset);

		return $this->handleView($this->view([
			'data' => $conversations
		], 200));
	}

	/**
	 * @Rest\Post("conversations/{conversationId}", requirements={"conversationId" = "\d+"})
	 * @Rest\RequestParam(name="body", nullable=false)
	 */
	public function sendMessageAction(int $conversationId, ParamFetcher $paramFetcher)
	{
		if (!$this->session->may() || !$this->messageGateway->mayConversation($this->session->id(), $conversationId)) {
			throw new HttpException(401);
		}
		$body = $paramFetcher->get('body');
		$this->messageService->sendMessage($conversationId, $this->session->id(), $body);

		return $this->handleView($this->view([], 200));
	}

	/**
	 * @Rest\Post("user/{userId}/conversation")
	 */
	public function getUserConversationAction(int $userId)
	{
		if (!$this->session->may()) {
			throw new HttpException(401);
		}

		$conversationId = $this->messageGateway->getOrCreateConversation([$this->session->id(), $userId]);

		return $this->handleView($this->view(['id' => $conversationId], 200));
	}
}
