<?php

namespace Foodsharing\Modules\Stats;

use Foodsharing\Helpers\WeightHelper;
use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Core\DBConstants\Region\Type;

class StatsModel extends Db
{
	private $weightHelper;

	public function __construct(WeightHelper $weightHelper)
	{
		$this->weightHelper = $weightHelper;

		parent::__construct();
	}

	public function getFirstFetchInStore($storeId, $fsId)
	{
		return $this->qOne(
			'
				SELECT 
					MIN(`date`) 
				
				FROM 
					fs_abholer 
				
				WHERE 
					betrieb_id = ' . $storeId . ' 
				
				AND 
					foodsaver_id = ' . $fsId . '
				AND 
					`confirmed` = 1'
		);
	}

	public function getTotalKilosFetchedByFoodsaver(int $fs_id)
	{
		$savedWeight = 0;
		if ($queryResult = $this->qOne('
			SELECT 
			       sum(fw.weight) AS saved 
			FROM fs_abholer fa
				left outer join fs_betrieb fb on fa.betrieb_id = fb.id
				left outer join fs_fetchweight fw on fb.abholmenge = fw.id
			WHERE
			      fa.foodsaver_id = ' . $fs_id . '
			  AND fa.date < now()
			  AND fa.confirmed = 1
		')
		) {
			$savedWeight = $queryResult;
		}

		return $savedWeight;
	}

	public function getAllFoodsaverIds()
	{
		return $this->qCol('SELECT id FROM fs_foodsaver');
	}

	public function getLastFetchInStore($storeId, $fsId)
	{
		return $this->qOne(
			'
				SELECT
					MAX(`date`)
	
				FROM
					fs_abholer
	
				WHERE
					betrieb_id = ' . $storeId . '
	
				AND
					foodsaver_id = ' . $fsId . '
				AND
					`confirmed` = 1
				AND 
					`date` < NOW()'
		);
	}

	public function updateStoreStats(
		$betrieb_id,
		$foodsaver_id,
		$add_date,
		$first_fetch,
		$fetchcount,
		$last_fetch)
	{
		$this->update('
			UPDATE 	`fs_betrieb_team` 
				
			SET 	`stat_last_update` = NOW(),
					`stat_fetchcount` = ' . (int)$fetchcount . ',
					`stat_first_fetch` = ' . $this->dateval($first_fetch) . ',
					`stat_add_date` = ' . $this->dateval($add_date) . ',
					`stat_last_fetch` = ' . $this->dateval($last_fetch) . '
				
			WHERE 	`foodsaver_id` = ' . (int)$foodsaver_id . '
			AND 	`betrieb_id` = ' . (int)$betrieb_id . '		
		');
	}

	public function getStoreFetchCount($storeId, $fsId, $last_update, $stat_fetchcount)
	{
		$val = $this->qOne('
			SELECT COUNT(foodsaver_id)
					
			FROM 	fs_abholer
				
			WHERE 	`foodsaver_id` = ' . (int)$fsId . '
			AND 	`betrieb_id` = ' . (int)$storeId . '
			AND 	`date` > ' . $this->dateval($last_update) . '
			AND 	`date` < NOW()
			AND 	`confirmed` = 1
		');

		return (int)$val + (int)$stat_fetchcount;
	}

	public function updateStats($regionId, $fetchweight, $fetchcount, $postcount, $betriebcount, $korpcount, $botcount, $fscount, $foodSharePointCount)
	{
		return $this->update('

				UPDATE 	
					`fs_bezirk` 
				
				SET 
					`stat_last_update`= NOW(),
					`stat_fetchweight`=' . (float)$fetchweight . ',
					`stat_fetchcount`=' . (int)$fetchcount . ',
					`stat_postcount`=' . (int)$postcount . ',
					`stat_betriebcount`=' . (int)$betriebcount . ',
					`stat_korpcount`=' . (int)$korpcount . ',
					`stat_botcount`=' . (int)$botcount . ',
					`stat_fscount`=' . (int)$fscount . ',
					`stat_fairteilercount`=' . (int)$foodSharePointCount . ' 
				
				WHERE 
					`id` = ' . (int)$regionId . '
				
		');
	}

	public function getAllRegions()
	{
		return $this->q('SELECT id, name, stat_last_update FROM fs_bezirk');
	}

	public function getFairteilerCount($region_id, $child_ids)
	{
		$child_ids[$region_id] = $region_id;

		return (int)$this->qOne('SELECT COUNT(id) FROM fs_fairteiler WHERE bezirk_id IN(' . implode(',', $child_ids) . ')');
	}

	public function getCooperatingStoresCount($region_id, $child_ids)
	{
		$child_ids[$region_id] = $region_id;

		return (int)$this->qOne('SELECT COUNT(id) FROM fs_betrieb WHERE bezirk_id IN(' . implode(',', $child_ids) . ') AND betrieb_status_id IN(3,5)');
	}

	public function getStoreCount($region_id, $child_ids)
	{
		$child_ids[$region_id] = $region_id;

		return (int)$this->qOne('SELECT COUNT(id) FROM fs_betrieb WHERE bezirk_id IN(' . implode(',', $child_ids) . ')');
	}

	public function getPostCount($region_id, $child_ids)
	{
		$child_ids[$region_id] = $region_id;

		$stat_post = (int)$this->qOne('
			
			SELECT COUNT(p.id) 
				
			FROM 	fs_theme_post p,
					fs_bezirk_has_theme tb
				
			WHERE 	p.theme_id = tb.theme_id
			AND 	tb.bezirk_id IN(' . implode(',', $child_ids) . ')
				
		');

		$stat_post += (int)$this->qOne('
			SELECT 	COUNT(bn.id) 
			FROM 	fs_betrieb_notiz bn,
					fs_betrieb b
			WHERE 	bn.betrieb_id = b.id
			AND 	b.bezirk_id IN(' . implode(',', $child_ids) . ')
				
		');

		return $stat_post;
	}

	public function getBotCount($region_id, $child_ids)
	{
		$child_ids[$region_id] = $region_id;
		$out = [];
		if ($foodsaver = $this->q('
			SELECT 	amb.foodsaver_id AS id
			FROM 	fs_botschafter amb JOIN fs_bezirk region ON amb.bezirk_id = region.id
			WHERE 	amb.bezirk_id IN(' . implode(',', $child_ids) . ')
			AND NOT	region.type = ' . Type::WORKING_GROUP . '
		')
		) {
			foreach ($foodsaver as $fs) {
				$out[$fs['id']] = true;
			}
		}

		return count($out);
	}

	public function getFsCount($region_id, $child_ids)
	{
		$child_ids[$region_id] = $region_id;
		$out = [];
		if ($foodsaver = $this->q('
			SELECT 	fb.foodsaver_id AS id
			FROM 	fs_foodsaver_has_bezirk fb
			WHERE 	fb.bezirk_id IN(' . implode(',', $child_ids) . ')
		')
		) {
			foreach ($foodsaver as $fs) {
				$out[$fs['id']] = true;
			}
		}

		return count($out);
	}

	public function getFetchWeight($region_id, $last_update, $child_ids)
	{
		$child_ids[$region_id] = $region_id;
		//$current = floatval($this->getVal('stat_fetchweight', 'bezirk', $region_id));

		$weight = 0;
		$dat = [];
		if ($res = $this->q('
			SELECT 	a.betrieb_id, 
					a.`date`,
					b.abholmenge
					
			FROM   `fs_abholer` a,
			       `fs_betrieb` b
			WHERE 	a.betrieb_id =b.id
			
			AND   	a.`date` < NOW()
			AND 	a.`date` > ' . $this->dateval($last_update) . '
				
			AND 	b.bezirk_id IN(' . implode(',', $child_ids) . ')
		
		')
		) {
			foreach ($res as $r) {
				$dat[$r['betrieb_id'] . '-' . $r['date']] = $r;
			}
			foreach ($dat as $r) {
				$weight += $this->weightHelper->mapIdToKilos($r['abholmenge']);
			}
		}

		$current = $this->getValues(['stat_fetchweight', 'stat_fetchcount'], 'bezirk', $region_id);

		return [
			'weight' => ($weight + (int)$current['stat_fetchweight']),
			'count' => (count($dat) + (int)$current['stat_fetchcount'])
		];
	}
}
