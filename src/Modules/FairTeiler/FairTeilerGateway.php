<?php

namespace Foodsharing\Modules\FairTeiler;

use Foodsharing\Modules\Core\BaseGateway;
use function GuzzleHttp\Psr7\str;

class FairTeilerGateway extends BaseGateway
{
	public function getEmailFollower($id)
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`name`,
					fs.`nachname`,
					fs.`email`,
					fs.`geschlecht`
				
			FROM 	`fs_fairteiler_follower` ff,
					`fs_foodsaver` fs
				
			WHERE 	ff.foodsaver_id = fs.id
			AND 	ff.fairteiler_id = :id
			AND 	ff.infotype = 1
		', [':id' => $id]);
	}

	public function getLastFtPost($id)
	{
		return $this->db->fetch('
			SELECT 		wp.id,
						wp.time,
						UNIX_TIMESTAMP(wp.time) AS time_ts,
						wp.body,
						wp.attach,
						fs.name AS fs_name			
					
			FROM 		fs_fairteiler_has_wallpost hw 
			LEFT JOIN 	fs_wallpost wp
			ON 			hw.wallpost_id = wp.id
				
			LEFT JOIN 	fs_foodsaver fs ON wp.foodsaver_id = fs.id

			WHERE 		hw.fairteiler_id = :id
				
			ORDER BY 	wp.id DESC
			LIMIT 1
		', [':id' => $id]);
	}

	public function updateVerantwortliche($id)
	{
		global $g_data;
		$values = array();

		foreach ($g_data['bfoodsaver'] as $fs) {
			$values[] = '(' . (int)$id . ',' . (int)$fs . ',2,1)';
		}

		$this->db->update('fs_fairteiler_follower', ['type' => 1], ['fairteiler_id' => $id]);

		return $this->db->execute('
				REPLACE INTO `fs_fairteiler_follower`
				(
					`fairteiler_id`,
					`foodsaver_id`,
					`type`,
					`infotype`
				)
				VALUES
				' . implode(',', $values) . '
		');
	}

	public function getInfoFollower($id)
	{
		return $this->db->fetchAll('
			SELECT 	fs.`id`,
					fs.`name`,
					fs.`nachname`,
					fs.`email`,
					fs.sleep_status
	
			FROM 	`fs_fairteiler_follower` ff,
					`fs_foodsaver` fs
	
			WHERE 	ff.foodsaver_id = fs.id
			AND 	ff.fairteiler_id = ' . (int)$id . '
		');
	}

	public function listFairteiler($bezirk_id)
	{
		$bezirk_ids = array();
		if ($bezirk_id == 0) {
			if ($bezike = $this->getBezirke()) {
				foreach ($bezike as $b) {
					if ($bb = $this->getChildBezirke($b['id'])) {
						foreach ($bb as $c) {
							$bezirk_ids[$c] = $c;
						}
					}
				}
			}
		} else {
			if ($bb = $this->getChildBezirke($bezirk_id)) {
				foreach ($bb as $c) {
					$bezirk_ids[$c] = $c;
				}
			}
		}

		if (!empty($bezirk_ids) && ($fairteiler = $this->db->fetchAll('
			SELECT 	ft.`id`,
					ft.`name`,
					ft.`picture`,
					bz.id AS bezirk_id,
					bz.name AS bezirk_name
			
			FROM 	`fs_fairteiler` ft,
					`fs_bezirk` bz
				
			WHERE 	ft.bezirk_id = bz.id
			AND 	ft.`bezirk_id` IN(' . implode(',', $bezirk_ids) . ')
			AND 	ft.`status` = 1
		'))
		) {
			$out = array();

			foreach ($fairteiler as $key => $ft) {
				if (!isset($out[$ft['bezirk_id']])) {
					$out[$ft['bezirk_id']] = array(
						'id' => $ft['bezirk_id'],
						'name' => $ft['bezirk_name'],
						'fairteiler' => array()
					);
				}
				$pic = false;
				if (!empty($ft['picture'])) {
					$pic = array(
						'thumb' => 'images/' . str_replace('/', '/crop_1_60_', $ft['picture']),
						'head' => 'images/' . str_replace('/', '/crop_0_528_', $ft['picture']),
						'orig' => 'images/' . ($ft['picture'])
					);
				}
				$out[$ft['bezirk_id']]['fairteiler'][] = array(
					'id' => $ft['id'],
					'name' => $ft['name'],
					'picture' => $ft['picture'],
					'pic' => $pic
				);
			}

			return $out;
		}

		return false;
	}

	public function getFairteilerIds($fsId)
	{
		return $this->db->fetchAllValues('SELECT fairteiler_id FROM fs_fairteiler_follower WHERE foodsaver_id = :id', [':id' => $fsId]);
	}

	public function follow($ft_id, $fs_id, $infotype)
	{
		return $this->db->execute('
				INSERT IGNORE INTO `fs_fairteiler_follower`
				(
					`fairteiler_id`,
					`foodsaver_id`,
					`type`,
					`infotype`
				)
				VALUES
				(
					:ft_id,
					:fs_id,
					1,
					:infotype
				)
		', ['ft_id' => $ft_id, ':fs_id' => $fs_id, ':infotype' => $infotype]);
	}

	public function unfollow($ft_id, $fs_id)
	{
		return $this->db->execute('
				DELETE FROM `fs_fairteiler_follower`
				WHERE 	fairteiler_id = :ft_id
				AND 	foodsaver_id = :fs_id
		', ['ft_id' => $ft_id, ':fs_id' => $fs_id]);
	}

	public function getFollower($id)
	{
		if ($follower = $this->db->fetchAll('

			SELECT 	fs.`name`,
					fs.`nachname`,
					fs.`id`,
					fs.`photo`,
					ff.type,
					fs.sleep_status
				
			FROM 	fs_foodsaver fs,
					fs_fairteiler_follower ff
			WHERE 	ff.foodsaver_id = fs.id
			AND 	ff.fairteiler_id = :id
				
		', [':id' => $id])
		) {
			$normal = array();
			$verantwortliche = array();
			$all = array();
			foreach ($follower as $f) {
				if ($f['type'] == 1) {
					$normal[] = $f;
					$all[$f['id']] = 'follow';
				} elseif ($f['type'] == 2) {
					$verantwortliche[] = $f;
					$all[$f['id']] = 'verantwortlich';
				}
			}

			return array(
				'follow' => $normal,
				'verantwortlich' => $verantwortliche,
				'all' => $all
			);
		}

		return false;
	}

	public function acceptFairteiler($id)
	{
		return $this->db->execute('
			UPDATE 	`fs_fairteiler`
		
			SET 	`status` = 1
		
			WHERE 	`id` = ' . $this->intval($id) . '
		');
	}

	public function updateFairteiler($id, $bezirk_id, $name, $desc, $anschrift, $plz, $ort, $lat, $lon, $picture)
	{
		$this->db->requireExists('fs_fairteiler', ['id' => $id]);
		$params = [
			'bezirk_id' => $bezirk_id,
			'name' => strip_tags($name),
			'desc' => strip_tags($desc),
			'anschrift' => strip_tags($anschrift),
			'plz' => strip_tags($plz),
			'ort' => strip_tags($ort),
			'lat' => strip_tags($lat),
			'lon' => strip_tags($lon),
		];

		if (!empty($picture)) {
			$params['picture'] = strip_tags($picture);
		}

		$this->db->update('fs_fairteiler', $params, ['id' => $id]);

		return true;
	}

	public function deleteFairteiler($id)
	{
		$this->db->execute('
			DELETE FROM 	`fs_fairteiler_follower`	
			WHERE `fairteiler_id` = ' . (int)$id . '
		');

		return $this->db->execute('
			DELETE FROM 	`fs_fairteiler`	
			WHERE `id` = ' . (int)$id . '	
		');
	}

	public function getFairteiler($id)
	{
		if ($ft = $this->db->fetch('
			SELECT 	ft.id,
					ft.`bezirk_id`,
					ft.`name`,
					ft.`picture`,
					ft.`status`,
					ft.`desc`,
					ft.`anschrift`,
					ft.`plz`,
					ft.`ort`,
					ft.`lat`,
					ft.`lon`,
					ft.`add_date`,
					UNIX_TIMESTAMP(ft.`add_date`) AS time_ts,
					ft.`add_foodsaver`,
					fs.name AS fs_name,
					fs.nachname AS fs_nachname,
					fs.id AS fs_id	
				
			FROM 	fs_fairteiler ft
			LEFT JOIN
					fs_foodsaver fs
					
				
			ON 	ft.add_foodsaver = fs.id
			WHERE 	ft.id = ' . (int)$id . '
		')
		) {
			$ft['pic'] = false;
			if (!empty($ft['picture'])) {
				$ft['pic'] = array(
					'thumb' => 'images/' . str_replace('/', '/crop_1_60_', $ft['picture']),
					'head' => 'images/' . str_replace('/', '/crop_0_528_', $ft['picture']),
					'orig' => 'images/' . ($ft['picture'])
				);
			}

			return $ft;
		}

		return false;
	}

	public function addFairteiler(
		$fs_id,
		$bezirk_id,
		$name,
		$desc,
		$anschrift,
		$plz,
		$ort,
		$lat,
		$lon,
		$picture = '',
		$status = 1)
	{
		if ($ftid = $this->db->execute('
			INSERT INTO 	`fs_fairteiler`
			(
				`bezirk_id`,
				`name`,
				`picture`,
				`status`,
				`desc`,
				`anschrift`,
				`plz`,
				`ort`,
				`lat`,
				`lon`,
				`add_date`,
				`add_foodsaver`
			)
			VALUES
			(
				' . $this->intval($bezirk_id) . ',
				' . $this->strval($name) . ',
				' . $this->strval($picture) . ',
				' . $this->intval($status) . ',
				' . $this->strval($desc) . ',
				' . $this->strval($anschrift) . ',
				' . $this->strval($plz) . ',
				' . $this->strval($ort) . ',
				' . $this->strval($lat) . ',
				' . $this->strval($lon) . ',
				NOW(),
				:fs_id
			)
		', [':fs_id' => $fs_id])
		) {
			$this->db->execute('
				REPLACE INTO `fs_fairteiler_follower`
				(
					`fairteiler_id`,
					`foodsaver_id`,
					`type`
				)
				VALUES
				(
					' . (int)$ftid . ',
					:fs_id,
					2
				)
			', [':fs_id' => $fs_id]);

			return $ftid;
		}
	}
}
