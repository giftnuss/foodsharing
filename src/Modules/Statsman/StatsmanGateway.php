<?php

namespace Foodsharing\Modules\Statsman;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Modules\Core\DBConstants\Store\CooperationStatus;

class StatsmanGateway extends BaseGateway
{
	public function cleanUpTotalFetchQuantities(): void
	{
		$this->db->delete('fs_stat_abholmengen', []);
	}

	public function listStores(): array
	{
		return $this->db->fetchAll('SELECT id FROM fs_betrieb');
	}

	public function insertWeightsToFetchQuantities(int $storeId): void
	{
		$this->db->execute(
			'INSERT INTO fs_stat_abholmengen
						   SELECT b.id, a.`date`, m.weight
						   FROM fs_betrieb b
						   INNER JOIN fs_abholer a ON a.betrieb_id = b.id
						   INNER JOIN fs_fetchweight m ON m.id = b.abholmenge
						   WHERE b.id = ' . $storeId . '
						   AND a.confirmed = 1
						   GROUP BY a.`date`'
		);
	}

	public function listRegionParents(): array
	{
		return $this->db->fetchAll('SELECT bezirk_id, GROUP_CONCAT(ancestor_id) AS parents FROM fs_bezirk_closure WHERE ancestor_id != 0 GROUP BY bezirk_id');
	}

	public function listRegionFetchQuantities(): array
	{
		return $this->db->fetchAll('
			SELECT b.id, b.name, YEARWEEK( m.`date` ) AS yw, SUM( m.abholmenge ) AS total, COUNT( m.abholmenge) AS cnt
			FROM fs_bezirk b
			INNER JOIN fs_betrieb btr ON ( btr.bezirk_id = b.id ) 
			INNER JOIN fs_stat_abholmengen m ON ( m.betrieb_id = btr.id ) 
			GROUP BY b.id, YEARWEEK( m.`date` ) 
			ORDER BY b.name');
	}

	public function listRegionClosures(): array
	{
		return $this->db->fetchAll("
			SELECT b.id, b.type, group_concat(b.name ORDER BY a.depth DESC SEPARATOR ' -> ') AS path
			FROM fs_bezirk_closure d
			JOIN fs_bezirk_closure a ON (a.bezirk_id = d.bezirk_id)
			JOIN fs_bezirk b ON (b.id = a.ancestor_id)
			WHERE d.ancestor_id = :region_europe AND d.bezirk_id != d.ancestor_id
			GROUP BY d.bezirk_id
			HAVING b.`type` != :working_group
			ORDER BY path",
			[':working_group' => Type::WORKING_GROUP, ':region_europe' => RegionIDs::EUROPE]);
	}

	public function listFoodsaversByAgeBands(): array
	{
		return $this->db->fetchAll("
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
				 id, geschlecht, bezirk_id FROM fs_foodsaver WHERE rolle >= :foodsaver_role AND bezirk_id >= 1
				) AS tbl
				GROUP BY ageband, geschlecht, bezirk_id",
			[':foodsaver_role' => Role::FOODSAVER]);
	}

	public function listFoodsaversByRegionRegistration(): array
	{
		return $this->db->fetchAll('
				SELECT
				COUNT(id) AS cnt,
				YEARWEEK(anmeldedatum) AS yw, bezirk_id
				FROM
				fs_foodsaver WHERE rolle >= :foodsaver_role AND bezirk_id >= 1
				GROUP BY yw, bezirk_id',
			[':foodsaver_role' => Role::FOODSAVER]);
	}

	public function listStoresByAddition(): array
	{
		return $this->db->fetchAll('
			SELECT b.id, b.name, YEARWEEK( btr.added ) AS yw, COUNT(btr.id) AS cnt
			FROM fs_bezirk b
			INNER JOIN fs_betrieb btr ON ( btr.bezirk_id = b.id ) 
			WHERE btr.betrieb_status_id = :cooperationStatus
			GROUP BY b.id, YEARWEEK( btr.added )',
			[':cooperationStatus' => CooperationStatus::COOPERATION_STARTING]);
	}
}
