<?php

namespace Foodsharing\Controller;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Message\MessageModel;
use FOS\RestBundle\Controller\FOSRestController;
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
		} elseif ($this->model->mayConversation($conversation_id)) {
			$ids[$conversation_id] = true;
			$this->session->set('msg_conversations', $ids);

			return true;
		}

		return false;
	}

	public function getConversationAction($id)
	{
		if (!$this->session->may() || !$this->mayConversation($id)) {
			throw new HttpException(401);
		}

		$member = $this->model->listConversationMembers($id);
		$messages = $this->model->loadConversationMessages($id);
		$conversation = $this->model->getValues(array('name'), 'conversation', $id);

		$data = [
			'conversation' => $conversation,
			'member' => $member,
			'messages' => $messages,
		];

		$view = $this->view($data, 200);

		return $this->handleView($view);
	}
}
