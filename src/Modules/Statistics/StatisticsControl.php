<?php

namespace Foodsharing\Modules\Statistics;

use Foodsharing\Modules\Content\ContentGateway;
use Foodsharing\Modules\Core\Control;

class StatisticsControl extends Control
{
	private $contentGateway;
	private $statisticsGateway;

	public function __construct(StatisticsGateway $statisticsGateway, StatisticsView $view, ContentGateway $contentGateway)
	{
		$this->statisticsGateway = $statisticsGateway;
		$this->view = $view;
		$this->contentGateway = $contentGateway;

		parent::__construct();
	}

	public function index()
	{
		$content = $this->contentGateway->get(11);

		$this->func->addTitle($content['title']);

		$this->func->addBread($content['title']);

		$stat_gesamt = $this->statisticsGateway->getStatGesamt();

		$stat_cities = $this->statisticsGateway->getStatCities();

		foreach ($stat_cities as $i => $c) {
			$stat_cities[$i]['percent'] = $this->getPercent($stat_gesamt['fetchweight'], $c['fetchweight']);
		}

		$stat_fs = $this->statisticsGateway->getStatFoodsaver();

		$this->func->addContent($this->view->getStatTotal($stat_gesamt), CNT_TOP);

		$this->func->addContent($this->view->getStatCities($stat_cities), CNT_LEFT);
		$this->func->addContent($this->view->getStatFoodsaver($stat_fs), CNT_RIGHT);

		$this->setContentWidth(12, 12);
	}

	private function getPercent($gesamt, $teil)
	{
		if ($gesamt) {
			return round(($teil / ($gesamt / 100)), 0);
		}

		return 0;
	}
}
