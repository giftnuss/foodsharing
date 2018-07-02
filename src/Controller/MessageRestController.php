<?php

namespace Foodsharing\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Foodsharing\Modules\Message\MessageModel;
use Foodsharing\Lib\Session\S;

class MessageRestController extends FOSRestController
{
	private $model;

	public function __construct(MessageModel $model)
	{
		$this->model = $model;
	}

	private function handleUnauthorized()
	{
		$view = $this->view([
			'statusCode' => 401,
			'message' => 'Unauthorized'
		], 401);

		return $this->handleView($view);
	}

	// TODO: this is copied directly from from messageXhr.php
	private function mayConversation($conversation_id)
	{
		// first get the session array
		if (!($ids = S::get('msg_conversations'))) {
			$ids = [];
		}

		// check if the conversation in stored in the session
		if (isset($ids[(int)$conversation_id])) {
			return true;
		} elseif ($this->model->mayConversation($conversation_id)) {
			$ids[$conversation_id] = true;
			S::set('msg_conversations', $ids);

			return true;
		}

		return false;
	}

	public function getConversationAction($id)
	{
		if (!S::may() || !$this->mayConversation($id)) {
			return $this->handleUnauthorized();
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
