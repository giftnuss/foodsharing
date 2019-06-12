<?php

namespace Foodsharing\Modules\Message;

use Foodsharing\Lib\WebSocketSender;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Store\StoreGateway;

final class MessageXhr extends Control
{
	private $messageGateway;
	private $storeGateway;
	/**
	 * @var WebSocketSender
	 */
	private $webSocketSender;

	public function __construct(MessageModel $model, MessageView $view, MessageGateway $messageGateway, StoreGateway $storeGateway, WebSocketSender $webSocketSender)
	{
		$this->model = $model;
		$this->view = $view;
		$this->messageGateway = $messageGateway;
		$this->storeGateway = $storeGateway;
		$this->webSocketSender = $webSocketSender;

		parent::__construct();

		if (!$this->session->may()) {
			echo '';
			exit();
		}
	}

	public function people(): void
	{
		$this->session->noWrite();

		$term = trim($_GET['term']);
		if ($people = $this->model->findConnectedPeople($term)) {
			echo json_encode($people);
			exit();
		}

		echo json_encode(array());
		exit();
	}
}
