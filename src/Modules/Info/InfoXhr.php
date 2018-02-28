<?php

namespace Foodsharing\Modules\Info;

use Foodsharing\Lib\Session\S;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Modules\Core\Control;

class InfoXhr extends Control
{
	private $info;

	public function __construct(InfoModel $model)
	{
		$this->model = $model;

		$this->info = array();

		parent::__construct();
	}

	public function initbadge()
	{
		$xhr = new Xhr();

		$bell = (int)$this->model->qOne('SELECT COUNT(bell_id) FROM ' . PREFIX . 'foodsaver_has_bell WHERE foodsaver_id = ' . (int)$this->func->fsId() . ' AND seen = 0');

		// extra bells for betrieb
		if (isset($_SESSION['client']['verantwortlich']) && is_array($_SESSION['client']['verantwortlich'])) {
			$ids = array();
			foreach ($_SESSION['client']['verantwortlich'] as $v) {
				$ids[] = (int)$v['betrieb_id'];
			}
			if (!empty($ids)) {
				$bell += (int)$this->model->qOne('SELECT COUNT( betrieb_id ) FROM fs_abholer a WHERE betrieb_id IN(' . implode(',', $ids) . ') AND confirmed = 0 AND `date` > NOW() ');
			}
		}
		// get new Fair-Teiler badgecount only for region admin
		if (S::may('bot')) {
			if ($count = $this->model->getFairteilerBadgdeCount()) {
				$bell += $count;
			}
		}

		$xhr->addData('bell', $bell);
		$xhr->addData('msg', (int)$this->model->qOne('SELECT COUNT(conversation_id) FROM ' . PREFIX . 'foodsaver_has_conversation WHERE foodsaver_id = ' . (int)$this->func->fsId() . ' AND unread = 1'));
		$xhr->addData('basket', 0);

		$xhr->send();
	}
}
