<?php

namespace Foodsharing\Modules\Statsman;

use Foodsharing\Modules\Console\ConsoleControl;

class StatsmanControl extends ConsoleControl
{
	private $statsmanGateway;

	public function __construct(StatsmanGateway $statsmanGateway)
	{
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

	public function out_bezirk_stat(): void
	{
		// array of arrays -> [bezirk_id] = array(parent1, parent2, ..)
		$region_parents = $this->get_parent_bezirke();
		// 2 dimensional array [bezirk_id][yearweek] = sum
		$region_yw_sum = [];
		$region_yw_cnt = [];
		$all_yw = [];
		$q = $this->statsmanGateway->listRegionFetchQuantities();
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
		$fp = fopen('stat_out.txt', 'wb');
		$fpc = fopen('stat_cnt.txt', 'wb');
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

		$q = $this->statsmanGateway->listRegionClosures();
		foreach ($q as $line) {
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

	private function get_parent_bezirke(): array
	{
		$region_parents = [];
		$parents = $this->statsmanGateway->listRegionParents();
		foreach ($parents as $parent) {
			$a = explode(',', $parent['parents']);
			if (!is_array($a)) {
				$a = [$a];
			}
			$region_parents[$parent['bezirk_id']] = $a;
		}

		return $region_parents;
	}

	public function out_fs_by_bezirk_age(): void
	{
		$fp = fopen('stat_fs_bezirk.csv', 'wb');
		$foodsaver = $this->statsmanGateway->listFoodsaversByAgeBands();
		$parents = $this->get_parent_bezirke();
		$ages = [];
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

		$q = $this->statsmanGateway->listRegionClosures();
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
		$foodsaver = $this->statsmanGateway->listFoodsaversByRegionRegistration();
		$parents = $this->get_parent_bezirke();
		$all_yw = [];
		$ages = [];
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
		$this->writeResultsToFile($fp, $all_yw, $ages);
	}

	public function out_betriebe_eintrag(): void
	{
		// array of arrays -> [bezirk_id] = array(parent1, parent2, ..)
		$region_parents = $this->get_parent_bezirke();
		// 2 dimensional array [bezirk_id][yearweek] = sum
		$region_yw_cnt = [];
		$all_yw = [];
		$q = $this->statsmanGateway->listStoresByAddition();
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
		$this->writeResultsToFile($fp, $all_yw, $region_yw_cnt);
		fclose($fp);
	}

	/**
	 * @param $fp
	 */
	private function writeResultsToFile($fp, array $all_yw, array $data): void
	{
		fwrite($fp, 'bezirk,');
		$yws = array_keys($all_yw);
		sort($yws);
		foreach ($yws as $yw) {
			fwrite($fp, ",$yw");
		}
		fwrite($fp, "\n");

		$q = $this->statsmanGateway->listRegionClosures();
		foreach ($q as $line) {
			echo $line['path'];
			fwrite($fp, '"' . $line['path'] . '"');
			foreach ($yws as $yw) {
				$sum = 0;
				if (array_key_exists($line['id'], $data)
					&& array_key_exists($yw, $data[$line['id']])
				) {
					$sum = $data[$line['id']][$yw];
				}
				fwrite($fp, ",$sum");
			}
			fwrite($fp, "\n");
		}
	}
}
