<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Message\MessageGateway;
use Foodsharing\Modules\Message\MessageModel;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MessageRestController extends AbstractFOSRestController
{
	private $model;
	private $messageGateway;
	private $session;

	public function __construct(MessageModel $model, MessageGateway $messageGateway, Session $session)
	{
		$this->model = $model;
		$this->messageGateway = $messageGateway;
		$this->session = $session;
	}

	/**
	 * @Rest\Get("conversations/{conversationId}", requirements={"conversationId" = "\d+"}, defaults={"messagesNumber" = 20})
	 * @Rest\QueryParam(name="messagesLimit", requirements="\d+", default="20", description="How many messages to return.")
	 * @Rest\QueryParam(name="messagesOffset", requirements="\d+", default="0", description="Offset returned messages.")
	 */
	public function getConversationAction(int $conversationId, ParamFetcher $paramFetcher)
	{
		if (!$this->session->may() || !$this->messageGateway->mayConversation($this->session->id(), $conversationId)) {
			throw new HttpException(401);
		}

		$conversationData = $this->getConversationData($paramFetcher, $conversationId);

		$view = $this->view($conversationData, 200);

		return $this->handleView($view);
	}

	private function getConversationData(ParamFetcher $paramFetcher, $conversationId)
	{
		$messagesLimit = $paramFetcher->get('messagesLimit');
		$messagesOffset = $paramFetcher->get('messagesOffset');

		$members = $this->model->listConversationMembers($conversationId);
		$publicMemberInfo = function ($member) {
			return RestNormalization::normalizeFoodsaver($member);
		};
		$members = array_map($publicMemberInfo, $members);

		$messages = $this->messageGateway->getConversationMessages($conversationId, $messagesLimit, $messagesOffset);
		$name = $this->messageGateway->getConversationName($conversationId);
		$this->model->setAsRead([$conversationId]);

		$conversationData = [
			'name' => $name,
			'member' => $members, // remove this in the future once clients have updated
			'members' => $members,
			'messages' => $messages,
		];

		return $conversationData;
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
}
