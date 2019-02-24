<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\Control;

final class MessageControl extends Control
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

	public function index(): void
	{
		$this->setTemplate('msg');
		$this->setContentWidth(5, 8);

		$this->pageCompositionHelper->addJs('msg.fsid = ' . (int)$this->session->id() . ';');
		$this->pageCompositionHelper->addBread($this->func->s('messages'));
		$this->pageCompositionHelper->addTitle($this->func->s('messages'));

		$this->pageCompositionHelper->addContent($this->view->compose());
		$this->pageCompositionHelper->addContent($this->view->conversation());
		$this->pageCompositionHelper->addContent($this->view->leftMenu(), CNT_RIGHT);

		$conversations = $this->model->listConversations();
		if ($conversations) {
			$ids = array();
			foreach ($conversations as $c) {
				$ids[$c['id']] = true;
			}
			$this->session->set('msg_conversations', $ids);
		}
		$this->pageCompositionHelper->addContent($this->view->conversationListWrapper($this->view->conversationList($conversations)), CNT_RIGHT);
	}
}
