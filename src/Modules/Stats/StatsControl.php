<?php

namespace Foodsharing\Modules\Stats;

use Foodsharing\Modules\Console\ConsoleControl;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;

class StatsControl extends ConsoleControl
{
	private $model;
	private $storeGateway;
	private $regionGateway;
	private $statsGateway;

	public function __construct(
		StatsModel $model,
		StoreGateway $storeGateway,
		RegionGateway $regionGateway,
		StatsGateway $statsGateway
	) {
		$this->model = $model;
		$this->statsGateway = $statsGateway;
		$this->storeGateway = $storeGateway;
		$this->regionGateway = $regionGateway;
		parent::__construct();
	}

	public function foodsaver()
	{
		self::info('Statistik Auswertung für Foodsaver');

		if ($allFsIds = $this->model->getAllFoodsaverIds()) {
			foreach ($allFsIds as $fsid) {
				$totalKilosFetchedByFoodsaver = $this->model->getTotalKilosFetchedByFoodsaver($fsid);
				$stat_fetchcount = (int)$this->model->qOne(
					'SELECT COUNT(foodsaver_id) FROM fs_abholer WHERE foodsaver_id = ' . (int)$fsid . ' AND `date` < NOW() AND confirmed = 1'
				);
				$stat_post = (int)$this->model->qOne(
					'SELECT COUNT(id) FROM fs_theme_post WHERE foodsaver_id = ' . (int)$fsid
				);
				$stat_post += (int)$this->model->qOne(
					'SELECT COUNT(id) FROM fs_wallpost WHERE foodsaver_id = ' . (int)$fsid
				);
				$stat_post += (int)$this->model->qOne(
					'SELECT COUNT(id) FROM fs_betrieb_notiz WHERE milestone = 0 AND foodsaver_id = ' . (int)$fsid
				);

				$stat_bananacount = (int)$this->model->qOne(
					'SELECT COUNT(foodsaver_id) FROM fs_rating WHERE `ratingtype` = 2 AND foodsaver_id = ' . (int)$fsid
				);

				$stat_buddycount = (int)$this->model->qone(
					'SELECT COUNT(foodsaver_id) FROM fs_buddy WHERE foodsaver_id = ' . (int)$fsid . ' AND confirmed = 1'
				);

				$stat_fetchrate = 100;

				$count_not_fetch = (int)$this->model->qOne(
					'SELECT COUNT(foodsaver_id) FROM fs_report WHERE `reporttype` = 1 AND committed = 1 AND tvalue like \'%Ist gar nicht zum Abholen gekommen%\' AND foodsaver_id = ' . (int)$fsid
				);

				if ($count_not_fetch > 0 && $stat_fetchcount >= $count_not_fetch) {
					$stat_fetchrate = round(100 - ($count_not_fetch / ($stat_fetchcount / 100)), 2);
				}

				$this->model->update(
					'
						UPDATE fs_foodsaver

						SET 	stat_fetchweight = ' . (float)$totalKilosFetchedByFoodsaver . ',
						stat_fetchcount = ' . (int)$stat_fetchcount . ',
						stat_postcount = ' . (int)$stat_post . ',
						stat_buddycount = ' . (int)$stat_buddycount . ',
						stat_bananacount = ' . (int)$stat_bananacount . ',
						stat_fetchrate = ' . (float)$stat_fetchrate . '

						WHERE 	id = ' . (int)$fsid . '
				'
				);
			}
		}

		self::success('foodsaver ready :o)');
	}

	public function betriebe()
	{
		self::info('Statistik Auswertung für Betriebe');

		$allStores = $this->statsGateway->fetchAllStores();

		foreach ($allStores as $store) {
			$this->calcStores($store);
		}

		self::success('stores ready :o)');
	}

	private function calcStores($store)
	{
		$storeId = $store['id'];

		if ($storeId > 0) {
			$added = $store['added'];

			if ($team = $this->storeGateway->getStoreTeam($storeId)) {
				foreach ($team as $fs) {
					$newdata = [
						'stat_first_fetch' => $fs['stat_first_fetch'],
						'foodsaver_id' => $fs['id'],
						'betrieb_id' => $storeId,
						'verantwortlich' => $fs['verantwortlich'],
						'stat_fetchcount' => $fs['stat_fetchcount'],
						'stat_last_fetch' => null,
					];

					/* first_fetch */
					if ($first_fetch = $this->model->getFirstFetchInStore($storeId, $fs['id'])) {
						$newdata['stat_first_fetch'] = $first_fetch;
					}

					/*last_fetch*/
					if ($last_fetch = $this->model->getLastFetchInStore($storeId, $fs['id'])) {
						$newdata['stat_last_fetch'] = $last_fetch;
					}

					/*fetchcount*/
					$fetchcount = $this->model->getStoreFetchCount(
						$storeId,
						$fs['id'],
						$fs['stat_last_update'],
						$fs['stat_fetchcount']
					);

					$this->model->updateStoreStats(
						$storeId, // Betrieb id
						$fs['id'], // foodsaver_id
						$fs['stat_add_date'], // add date
						$newdata['stat_first_fetch'], // erste mal abholen
						$fetchcount, // anzahl abholungen
						$newdata['stat_last_fetch']
					);
				}
			}
		}
	}

	/**
	 * public accessible method to calculate all statistics for each region
	 * for the moment I have no other idea to calculate live because the hierarchical child region query takes to long.
	 */
	public function bezirke()
	{
		self::info('Statistik Auswertung für Bezirke');

		// get all regions non memcached
		$allRegions = $this->model->getAllRegions();
		foreach ($allRegions as $region) {
			$this->calcRegion($region);
		}

		self::success('region ready :o)');
	}

	private function calcRegion($region)
	{
		$region_id = $region['id'];
		$last_update = $region['stat_last_update'];

		$child_ids = $this->regionGateway->listIdsForDescendantsAndSelf($region_id);

		/* abholmenge & anzahl abholungen */
		$stat_fetchweight = $this->model->getFetchWeight($region_id, $last_update, $child_ids);
		$stat_fetchcount = $stat_fetchweight['count'];
		$stat_fetchweight = $stat_fetchweight['weight'];

		/* anzahl foodsaver */
		$stat_fscount = $this->model->getFsCount($region_id, $child_ids);

		/*anzahl botschafter*/
		$stat_botcount = $this->model->getBotCount($region_id, $child_ids);

		/* anzahl posts */
		$stat_postcount = $this->model->getPostCount($region_id, $child_ids);

		/* fairteiler_count */
		$stat_fairteilercount = $this->model->getFairteilerCount($region_id, $child_ids);

		/* count betriebe */
		$stat_betriebecount = $this->model->getStoreCount($region_id, $child_ids);

		/* count koorp betriebe */
		$stat_betriebCoorpCount = $this->model->getCooperatingStoresCount($region_id, $child_ids);

		$this->model->updateStats(
			$region_id,
			$stat_fetchweight,
			$stat_fetchcount,
			$stat_postcount,
			$stat_betriebecount,
			$stat_betriebCoorpCount,
			$stat_botcount,
			$stat_fscount,
			$stat_fairteilercount
		);

		return $stat_fetchweight;
	}
}
