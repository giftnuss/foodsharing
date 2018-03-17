<?php

namespace Foodsharing\Modules\Index;

use Foodsharing\Modules\Content\ContentModel;
use Foodsharing\Modules\Core\Control;

class IndexControl extends Control
{
	private $contentModel;

	public function __construct(IndexModel $model, IndexView $view, ContentModel $contentModel)
	{
		$this->model = $model;
		parent::__construct();
		$this->contentModel = $contentModel;
		$this->view = $view;
	}

	public function index()
	{
		$this->func->addTitle('Rette mit!');

		$this->func->addScript('/js/jquery.animatenumber.min.js');

		$gerettet = (int)$this->model->getGerettet();

		if ($gerettet == 0) {
			$gerettet = 762338;
		}

		$gerettet = round($gerettet, 0);

		if (strpos($_SERVER['HTTP_HOST'], 'foodsharing.at') !== false) {
			$page_content = $this->contentModel->getContent(37);
		} elseif (strpos($_SERVER['HTTP_HOST'], 'foodsharingschweiz.ch') !== false) {
			$page_content = $this->contentModel->getContent(47);
		} elseif (strpos($_SERVER['HTTP_HOST'], 'beta.foodsharing.de') !== false) {
			$page_content = $this->contentModel->getContent(48);
		} else {
			$page_content = $this->contentModel->getContent(38);
		}

		$this->func->addContent($this->view->index(
			$page_content['body'],
			$gerettet
		), CNT_OVERTOP);
	}
}
