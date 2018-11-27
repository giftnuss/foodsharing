<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Message\MessageModel;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MessageRestController extends FOSRestController
{
	private $model;
	private $session;

	public function __construct(MessageModel $model, Session $session)
	{
		$this->model = $model;
		$this->session = $session;
	}

	// TODO: this is copied directly from from messageXhr.php
	private function mayConversation($conversation_id)
	{
		// first get the session array
		if (!($ids = $this->session->get('msg_conversations'))) {
			$ids = [];
		}

		// check if the conversation in stored in the session
		if (isset($ids[(int)$conversation_id])) {
			return true;
		}

		if ($this->model->mayConversation($conversation_id)) {
			$ids[$conversation_id] = true;
			$this->session->set('msg_conversations', $ids);

			return true;
		}

		return false;
	}

	/**
	 * @Rest\Get("conversations/{conversationId}", requirements={"conversationId" = "\d+"}, defaults={"messagesNumber" = 20})
	 * @Rest\QueryParam(name="messagesLimit", requirements="\d+", default="20", description="How many messages to return.")
	 * @Rest\QueryParam(name="messagesOffset", requirements="\d+", default="0", description="Offset returned messages.")
	 */
	public function getConversationAction(int $conversationId, ParamFetcher $paramFetcher)
	{
		if (!$this->session->may() || !$this->mayConversation($conversationId)) {
			throw new HttpException(401);
		}

		$messagesLimit = $paramFetcher->get('messagesLimit');
		$messagesOffset = $paramFetcher->get('messagesOffset');

		$member = $this->model->listConversationMembers($conversationId);
		$publicMemberInfo = function ($member) {
			return [
				'id' => $member['id'],
				'name' => $member['name'],
				'photo' => $member['photo']
			];
		};
		$member = array_map($publicMemberInfo, $member);

		$messages = $this->model->loadConversationMessages($conversationId, $messagesLimit, $messagesOffset);
		$conversation = $this->model->getValues(array('name'), 'conversation', $conversationId);
		$this->model->setAsRead([$conversationId]);

		$data = [
			'conversation' => $conversation,
			'member' => $member,
			'messages' => $messages,
		];

		$view = $this->view($data, 200);

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

		$conversations = $this->model->listConversations($limit, $offset);
		$view = $this->view($conversations, 200);

		return $this->handleView($view);
	}
}