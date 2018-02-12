<?php

namespace Foodsharing\Modules\Index;

use Foodsharing\Modules\Content\ContentModel;
use Foodsharing\Modules\Core\Control;

class IndexControl extends Control
{
	public function __construct()
	{
		$this->model = new IndexModel();
		$this->view = new IndexView();

		parent::__construct();
	}

	public function index()
	{
		$db = new ContentModel();
		$this->func->addTitle('Rette mit!');

		$this->func->addScript('/js/jquery.animatenumber.min.js');

		$gerettet = (int)$this->model->getGerettet();

		if ($gerettet == 0) {
			$gerettet = 762338;
		}

		$gerettet = round($gerettet, 0);

		if (strpos($_SERVER['HTTP_HOST'], 'foodsharing.at') !== false) {
			$page_content = $db->getContent(37);
		} elseif (strpos($_SERVER['HTTP_HOST'], 'foodsharingschweiz.ch') !== false) {
			$page_content = $db->getContent(47);
		} elseif (strpos($_SERVER['HTTP_HOST'], 'beta.foodsharing.de') !== false) {
			$page_content = $db->getContent(48);
		} else {
			$page_content = $db->getContent(38);
		}

		$this->func->addContent($this->view->index(
			$page_content['body'],
			$gerettet
		), CNT_OVERTOP);
	}
}
