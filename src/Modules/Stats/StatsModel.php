<?php

namespace Foodsharing\Modules\Stats;

use Foodsharing\Helpers\WeightHelper;
use Foodsharing\Lib\Db\Db;

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

	public function getTotallyFetchedByFoodsaver(int $fs_id)
	{
		$out = 0;
		if ($stores = $this->q('
			SELECT COUNT(a.`betrieb_id`) AS anz, a.betrieb_id, b.abholmenge
			FROM   `fs_abholer` a,
			       `fs_betrieb` b
			WHERE a.betrieb_id =b.id
			AND   foodsaver_id = ' . $fs_id . '
			AND   a.`date` < NOW()
			GROUP BY a.`betrieb_id`
	
	
		')
		) {
			foreach ($stores as $s) {
				$out += $this->weightHelper->mapIdToKilos($s['abholmenge']) * $s['anz'];
			}
		}

		return $out;
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

	// method currently not used. @fs_k wants to keep it in source for now.
	public function getBetriebTeam($storeId)
	{
		return $this->q('

			SELECT 
				t.stat_last_update,
				t.`stat_fetchcount`,
				t.`stat_first_fetch`,
				t.`stat_last_fetch`,
				UNIX_TIMESTAMP(t.`stat_first_fetch`) AS first_fetch_ts,
				t.`stat_add_date`,
				UNIX_TIMESTAMP(t.`stat_add_date`) AS add_date_ts,
				t.foodsaver_id,
				t.verantwortlich,
				t.active
				
			FROM 
				fs_betrieb_team t

			WHERE 
				t.betrieb_id = ' . (int)$storeId . '
				
		');
	}

	public function updateStats($regionId, $fetchweight, $fetchcount, $postcount, $betriebcount, $korpcount, $botcount, $fscount, $fairteilercount)
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
					`stat_fairteilercount`=' . (int)$fairteilercount . ' 
				
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
		$out = array();
		if ($foodsaver = $this->q('
			SELECT 	fb.foodsaver_id AS id
			FROM 	fs_botschafter fb
			WHERE 	fb.bezirk_id IN(' . implode(',', $child_ids) . ')
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
		$out = array();
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
		$current = floatval($this->getVal('stat_fetchweight', 'bezirk', $region_id));

		$weight = 0;
		$dat = array();
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

		$current = $this->getValues(array('stat_fetchweight', 'stat_fetchcount'), 'bezirk', $region_id);

		return array(
			'weight' => ($weight + (int)$current['stat_fetchweight']),
			'count' => (count($dat) + (int)$current['stat_fetchcount'])
		);
	}
}
