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
			$this->routeHelper->goLogin();
		}
	}

	public function index(): void
	{
		$this->setTemplate('msg');
		$this->setContentWidth(5, 8);

		$this->pageHelper->addJs('msg.fsid = ' . (int)$this->session->id() . ';');
		$this->pageHelper->addBread($this->translationHelper->s('messages'));
		$this->pageHelper->addTitle($this->translationHelper->s('messages'));

		$this->pageHelper->addContent($this->view->compose());
		$this->pageHelper->addContent($this->view->conversation());

		if (!$this->session->isMob()) { /* for desktop only */
			$this->pageHelper->addContent($this->view->leftMenu(), CNT_RIGHT);
		}

		$conversations = $this->model->listConversations();
		if ($conversations) {
			$ids = [];
			foreach ($conversations as $c) {
				$ids[$c['id']] = true;
			}
			$this->session->set('msg_conversations', $ids);
		}
		$this->pageHelper->addContent($this->view->conversationListWrapper($this->view->conversationList($conversations)), CNT_RIGHT);
	}
}
