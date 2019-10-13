<?php

namespace Foodsharing\Modules\Statsman;

use Foodsharing\Lib\Db\Db;
use Foodsharing\Modules\Console\ConsoleControl;

class StatsmanControl extends ConsoleControl
{
	private $statsmanGateway;

	public function __construct(Db $model, StatsmanGateway $statsmanGateway)
	{
		$this->model = $model;
		$this->statsmanGateway = $statsmanGateway;
		parent::__construct();
	}

	public function gen_abholmengen(): void
	{
		echo "inserting total fetch volumes into fs_stat_abholmengen...\n";
		$this->statsmanGateway->cleanUpTotalFetchQuantities();
		$stores = $this->statsmanGateway->listStores();
		foreach ($stores as $store) {
			$this->statsmanGateway->insertWeightsToFetchQuantities($store['id']);
			echo '.';
		}
		echo "\ndone";
	}

	private function get_parent_bezirke(): array
	{
		$region_parents = array();
		$parents = $this->model->q('SELECT bezirk_id, GROUP_CONCAT(ancestor_id) AS parents FROM fs_bezirk_closure WHERE ancestor_id != 0 GROUP BY bezirk_id');
		foreach ($parents as $p) {
			$a = explode(',', $p['parents']);
			if (!is_array($a)) {
				$a = [$a];
			}
			$region_parents[$p['bezirk_id']] = $a;
		}

		return $region_parents;
	}

	public function out_bezirk_stat(): void
	{
		// array of arrays -> [bezirk_id] = array(parent1, parent2, ..)
		$region_parents = $this->get_parent_bezirke();
		// 2 dimensional array [bezirk_id][yearweek] = sum
		$region_yw_sum = array();
		$region_yw_cnt = array();
		$all_yw = array();
		$q = $this->model->q('
			SELECT b.id, b.name, YEARWEEK( m.`date` ) AS yw, SUM( m.abholmenge ) AS total, COUNT( m.abholmenge) AS cnt
			FROM fs_bezirk b
			INNER JOIN fs_betrieb btr ON ( btr.bezirk_id = b.id ) 
			INNER JOIN fs_stat_abholmengen m ON ( m.betrieb_id = btr.id ) 
			GROUP BY b.id, YEARWEEK( m.`date` ) 
			ORDER BY b.name');
		foreach ($q as $line) {
			$all_yw[$line['yw']] = true;
			foreach ($region_parents[$line['id']] as $bzid) {
				if (!array_key_exists($bzid, $region_yw_sum)
					|| !array_key_exists($line['yw'], $region_yw_sum[$bzid])
				) {
					$region_yw_sum[$bzid][$line['yw']] = 0.0;
					$region_yw_cnt[$bzid][$line['yw']] = 0;
				}
				$region_yw_sum[$bzid][$line['yw']] += $line['total'];
				$region_yw_cnt[$bzid][$line['yw']] += $line['cnt'];
			}
		}
		$fp = fopen('stat_out.txt', 'w');
		$fpc = fopen('stat_cnt.txt', 'w');
		fwrite($fp, 'bezirk');
		fwrite($fpc, 'bezirk');
		$yws = array_keys($all_yw);
		sort($yws);
		foreach ($yws as $yw) {
			fwrite($fp, ",$yw");
			fwrite($fpc, ",$yw");
		}
		fwrite($fp, "\n");
		fwrite($fpc, "\n");

		$region_names = array();
		$q = $this->model->q("
			SELECT b.id, b.type, group_concat(b.name ORDER BY a.depth DESC SEPARATOR ' -> ') AS path
			FROM fs_bezirk_closure d
			JOIN fs_bezirk_closure a ON (a.bezirk_id = d.bezirk_id)
			JOIN fs_bezirk b ON (b.id = a.ancestor_id)
			WHERE d.ancestor_id = 741 AND d.bezirk_id != d.ancestor_id
			GROUP BY d.bezirk_id
			HAVING b.`type` != 7
			ORDER BY path");
		foreach ($q as $line) {
			$region_names[$line['id']] = $line['path'];
			echo $line['path'];
			fwrite($fp, '"' . $line['path'] . '"');
			fwrite($fpc, '"' . $line['path'] . '"');
			foreach ($yws as $yw) {
				$cnt = 0;
				$sum = 0;
				if (array_key_exists($line['id'], $region_yw_sum)
					&& array_key_exists($yw, $region_yw_sum[$line['id']])
				) {
					$sum = $region_yw_sum[$line['id']][$yw];
					$cnt = $region_yw_cnt[$line['id']][$yw];
				}
				fwrite($fp, ",$sum");
				fwrite($fpc, ",$cnt");
			}
			fwrite($fp, "\n");
			fwrite($fpc, "\n");
		}
		fclose($fp);
		fclose($fpc);
	}

	public function out_fs_by_bezirk_age(): void
	{
		$fp = fopen('stat_fs_bezirk.csv', 'wb');
		$foodsaver = $this->model->q("
				SELECT
				COUNT(*) AS cnt,
				CASE
				WHEN age < 18 THEN 'unknown'
				WHEN age >=18 AND age <=25 THEN '18-25'
				WHEN age >=26 AND age <=33 THEN '26-33'
				WHEN age >=34 AND age <=41 THEN '34-41'
				WHEN age >=42 AND age <=49 THEN '42-49'
				WHEN age >=50 AND age <=57 THEN '50-57'
				WHEN age >=58 AND age <=65 THEN '58-65'
				WHEN age >=66 AND age <=73 THEN '66-73'
				WHEN age >=74 AND age < 200 THEN '74+'
				WHEN age >= 200 THEN 'invalid'
				WHEN age IS NULL THEN 'unknown'
				END AS ageband, geschlecht, bezirk_id
				FROM
				(
				 SELECT DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(geb_datum, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(geb_datum, '00-%m-%d')) AS age,
				 id, geschlecht, bezirk_id FROM fs_foodsaver WHERE rolle >= 1 AND bezirk_id >= 1
				) AS tbl
				GROUP BY ageband, geschlecht, bezirk_id
				");
		$parents = $this->get_parent_bezirke();
		$ages = array();
		foreach ($foodsaver as $fs) {
			foreach ($parents[$fs['bezirk_id']] as $parent) {
				$cnt = $ages[$parent][$fs['ageband']][$fs['geschlecht']];
				if (!$cnt) {
					$cnt = 0;
				}
				$ages[$parent][$fs['ageband']][$fs['geschlecht']] = $cnt + $fs['cnt'];
			}
		}
		$ageBands = ['18-25', '26-33', '34-41', '42-49', '50-57', '58-65', '66-73', '74+', 'unknown', 'invalid'];
		fwrite($fp, 'bezirk,');
		fwrite($fp, implode(',', $ageBands));
		fwrite($fp, ',');
		fwrite($fp, implode(',', $ageBands));
		fwrite($fp, ',');
		fwrite($fp, implode(',', $ageBands));
		fwrite($fp, "\n");

		$q = $this->model->q("
			SELECT b.id, b.type, group_concat(b.name ORDER BY a.depth DESC SEPARATOR ' -> ') AS path
			FROM fs_bezirk_closure d
			JOIN fs_bezirk_closure a ON (a.bezirk_id = d.bezirk_id)
			JOIN fs_bezirk b ON (b.id = a.ancestor_id)
			WHERE d.ancestor_id = 741 AND d.bezirk_id != d.ancestor_id
			GROUP BY d.bezirk_id
			HAVING b.type != 7
			ORDER BY path");
		foreach ($q as $line) {
			echo $line['path'];
			fwrite($fp, '"' . $line['path'] . '"');
			$bzid = $line['id'];
			foreach ($ageBands as $ageband) {
				fwrite($fp, ',' . $ages[$bzid][$ageband][0]);
			}
			foreach ($ageBands as $ageband) {
				fwrite($fp, ',' . $ages[$bzid][$ageband][1]);
			}
			foreach ($ageBands as $ageband) {
				fwrite($fp, ',' . $ages[$bzid][$ageband][2]);
			}
			fwrite($fp, "\n");
		}
	}

	public function out_fs_by_bezirk_register(): void
	{
		$fp = fopen('stat_fs_bezirk_register.csv', 'wb');
		$foodsaver = $this->model->q('
				SELECT
				COUNT(id) AS cnt,
				YEARWEEK(anmeldedatum) AS yw, bezirk_id
				FROM
				fs_foodsaver WHERE rolle >= 1 AND bezirk_id >= 1
				GROUP BY yw, bezirk_id
				');
		$parents = $this->get_parent_bezirke();
		$all_yw = array();
		$ages = array();
		foreach ($foodsaver as $fs) {
			$all_yw[$fs['yw']] = true;
			foreach ($parents[$fs['bezirk_id']] as $parent) {
				$cnt = $ages[$parent][$fs['yw']];
				if (!$cnt) {
					$cnt = 0;
				}
				$ages[$parent][$fs['yw']] = $cnt + $fs['cnt'];
			}
		}
		fwrite($fp, 'bezirk,');
		$yws = array_keys($all_yw);
		sort($yws);
		foreach ($yws as $yw) {
			fwrite($fp, ",$yw");
		}
		fwrite($fp, "\n");

		$q = $this->model->q("
			SELECT b.id, b.type, group_concat(b.name ORDER BY a.depth DESC SEPARATOR ' -> ') AS path
			FROM fs_bezirk_closure d
			JOIN fs_bezirk_closure a ON (a.bezirk_id = d.bezirk_id)
			JOIN fs_bezirk b ON (b.id = a.ancestor_id)
			WHERE d.ancestor_id = 741 AND d.bezirk_id != d.ancestor_id
			GROUP BY d.bezirk_id
			HAVING b.`type` != 7
			ORDER BY path");
		foreach ($q as $line) {
			echo $line['path'];
			fwrite($fp, '"' . $line['path'] . '"');
			foreach ($yws as $yw) {
				$sum = 0;
				if (array_key_exists($line['id'], $ages)
					&& array_key_exists($yw, $ages[$line['id']])
				) {
					$sum = $ages[$line['id']][$yw];
				}
				fwrite($fp, ",$sum");
			}
			fwrite($fp, "\n");
		}
	}

	public function out_betriebe_eintrag(): void
	{
		// array of arrays -> [bezirk_id] = array(parent1, parent2, ..)
		$region_parents = $this->get_parent_bezirke();
		// 2 dimensional array [bezirk_id][yearweek] = sum
		$region_yw_cnt = array();
		$all_yw = array();
		$q = $this->model->q('
			SELECT b.id, b.name, YEARWEEK( btr.added ) AS yw, COUNT(btr.id) AS cnt
			FROM fs_bezirk b
			INNER JOIN fs_betrieb btr ON ( btr.bezirk_id = b.id ) 
			WHERE btr.betrieb_status_id = 3
			GROUP BY b.id, YEARWEEK( btr.added )');
		foreach ($q as $line) {
			$all_yw[$line['yw']] = true;
			foreach ($region_parents[$line['id']] as $bzid) {
				if (!array_key_exists($bzid, $region_yw_cnt)
					|| !array_key_exists($line['yw'], $region_yw_cnt[$bzid])
				) {
					$region_yw_cnt[$bzid][$line['yw']] = 0;
				}
				$region_yw_cnt[$bzid][$line['yw']] += $line['cnt'];
			}
		}
		$fp = fopen('betriebe_added.txt', 'wb');
		fwrite($fp, 'bezirk');
		$yws = array_keys($all_yw);
		sort($yws);
		foreach ($yws as $yw) {
			fwrite($fp, ",$yw");
		}
		fwrite($fp, "\n");

		$region_names = array();
		$q = $this->model->q("
			SELECT b.id, b.type, group_concat(b.name ORDER BY a.depth DESC SEPARATOR ' -> ') AS path
			FROM fs_bezirk_closure d
			JOIN fs_bezirk_closure a ON (a.bezirk_id = d.bezirk_id)
			JOIN fs_bezirk b ON (b.id = a.ancestor_id)
			WHERE d.ancestor_id = 741 AND d.bezirk_id != d.ancestor_id
			GROUP BY d.bezirk_id
			HAVING b.`type` != 7
			ORDER BY path");
		foreach ($q as $line) {
			$region_names[$line['id']] = $line['path'];
			echo $line['path'];
			fwrite($fp, '"' . $line['path'] . '"');
			foreach ($yws as $yw) {
				$cnt = 0;
				if (array_key_exists($line['id'], $region_yw_cnt)
					&& array_key_exists($yw, $region_yw_cnt[$line['id']])
				) {
					$cnt = $region_yw_cnt[$line['id']][$yw];
				}
				fwrite($fp, ",$cnt");
			}
			fwrite($fp, "\n");
		}
		fclose($fp);
	}
}
