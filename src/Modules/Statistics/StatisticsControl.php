<?php

namespace Foodsharing\Modules\Statistics;

use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;

class StatisticsControl extends Control
{
	private ContentGateway $contentGateway;
	private StatisticsGateway $statisticsGateway;

	public function __construct(
		StatisticsGateway $statisticsGateway,
		StatisticsView $view,
		ContentGateway $contentGateway
	) {
		$this->statisticsGateway = $statisticsGateway;
		$this->view = $view;
		$this->contentGateway = $contentGateway;

		parent::__construct();
	}

	public function index(): void
	{
		$content = $this->contentGateway->get(11);

		$this->pageHelper->addTitle($content['title']);
		$this->pageHelper->addBread($content['title']);

		$stat_total = $this->statisticsGateway->listTotalStat();
		$stat_total['totalBaskets'] = $this->statisticsGateway->countAllBaskets();
		$stat_total['avgWeeklyBaskets'] = $this->statisticsGateway->avgWeeklyBaskets();

		$stat_regions = $this->statisticsGateway->listStatRegions();
		$stat_fs = $this->statisticsGateway->listStatFoodsaver();

		$this->pageHelper->addContent($this->view->getStatTotal(
			$stat_total,
			$this->statisticsGateway->countAllFoodsharers(),
			$this->statisticsGateway->avgDailyFetchCount(),
			$this->statisticsGateway->countActiveFoodSharePoints()
		), CNT_TOP);
		$this->pageHelper->addContent($this->view->getStatRegions($stat_regions), CNT_LEFT);
		$this->pageHelper->addContent($this->view->getStatFoodsaver($stat_fs), CNT_RIGHT);

		$this->pageHelper->setContentWidth(12, 12);
	}
}
