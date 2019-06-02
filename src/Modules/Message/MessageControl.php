<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Modules\Core\Control;

final class MessageControl extends Control
{
	private $messageGateway;

	public function __construct(MessageModel $model, MessageGateway $messageGateway, MessageView $view)
	{
		$this->model = $model;
		$this->view = $view;
		$this->messageGateway = $messageGateway;

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

		$conversations = $this->messageGateway->listConversationsForUser($this->session->id());
		$this->pageHelper->addContent($this->view->conversationListWrapper($this->view->conversationList($conversations)), CNT_RIGHT);
	}
}
