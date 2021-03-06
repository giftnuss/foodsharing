<?php

namespace Foodsharing\Modules\Stats;

use Foodsharing\Modules\Console\ConsoleControl;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Modules\Store\StoreGateway;

class StatsControl extends ConsoleControl
{
	private StatsModel $model;
	private StoreGateway $storeGateway;
	private RegionGateway $regionGateway;
	private StatsGateway $statsGateway;

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
					'SELECT COUNT(foodsaver_id) FROM fs_rating WHERE foodsaver_id = ' . (int)$fsid
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
			if ($store['id'] > 0) {
				$this->statsGateway->updateStoreUsersData($store['id']);
			}
		}

		self::success('stores ready :o)');
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
