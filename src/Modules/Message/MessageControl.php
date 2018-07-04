<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\Control;

class MessageControl extends Control
{
	public function __construct(MessageModel $model, MessageView $view)
	{
		$this->model = $model;
		$this->view = $view;

		parent::__construct();

		if (!$this->session->may()) {
			$this->func->goLogin();
		}
	}

	public function index()
	{
		$this->setTemplate('msg');
		$this->setContentWidth(5, 8);

		$this->func->addJs('msg.fsid = ' . (int)$this->func->fsId() . ';');
		$this->func->addBread($this->func->s('messages'));
		$this->func->addTitle($this->func->s('messages'));

		$this->func->addContent($this->view->compose());
		$this->func->addContent($this->view->conversation());
		$this->func->addContent($this->view->leftMenu(), CNT_RIGHT);

		if ($conversations = $this->model->listConversations()) {
			$ids = array();
			foreach ($conversations as $c) {
				$ids[$c['id']] = true;
			}
			$this->session->set('msg_conversations', $ids);
		}
		$this->func->addContent($this->view->convListWrapper($this->view->conversationList($conversations)), CNT_RIGHT);
	}
}
