<?php

namespace Foodsharing\Modules\Index;

use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;

class IndexControl extends Control
{
	private $contentGateway;

	public function __construct(IndexModel $model, IndexView $view, ContentGateway $contentGateway)
	{
		$this->model = $model;
		parent::__construct();
		$this->contentGateway = $contentGateway;
		$this->view = $view;
	}

	public function index()
	{
		$this->func->addTitle('Rette mit!');

		$gerettet = (int)$this->model->getGerettet();

		if ($gerettet == 0) {
			$gerettet = 762338;
		}

		$gerettet = round($gerettet, 0);

		if (strpos($_SERVER['HTTP_HOST'], 'foodsharing.at') !== false) {
			$page_content = $this->contentGateway->getContent(37);
		} elseif (strpos($_SERVER['HTTP_HOST'], 'foodsharingschweiz.ch') !== false) {
			$page_content = $this->contentGateway->getContent(47);
		} elseif (strpos($_SERVER['HTTP_HOST'], 'beta.foodsharing.de') !== false) {
			$page_content = $this->contentGateway->getContent(48);
		} else {
			$page_content = $this->contentGateway->getContent(38);
		}

		$this->func->addContent($this->view->index(
			$page_content['body'],
			$gerettet
		), CNT_OVERTOP);
	}
}
