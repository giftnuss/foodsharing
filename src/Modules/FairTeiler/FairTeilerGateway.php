<?php

namespace Foodsharing\Modules\FairTeiler;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Region\RegionGateway;

class FairTeilerGateway extends BaseGateway
{
    private $regionGateway;

    public function __construct(Database $db, RegionGateway $regionGateway)
    {
        parent::__construct($db);
        $this->regionGateway = $regionGateway;
    }

    public function getEmailFollower($id)
    {
        return $this->db->fetchAll(
            '
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
		',
            [':id' => $id]
        );
    }

    public function getLastFtPost($id)
    {
        return $this->db->fetch(
            '
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
		',
            [':id' => $id]
        );
    }

    public function updateVerantwortliche($id, $bfoodsaver)
    {
        $values = array();

        foreach ($bfoodsaver as $fs) {
            $values[] = '(' . (int)$id . ',' . (int)$fs . ',2,1)';
        }

        $this->db->update('fs_fairteiler_follower', ['type' => 1], ['fairteiler_id' => $id]);

        $this->db->execute(
            '
				REPLACE INTO `fs_fairteiler_follower`
				(
					`fairteiler_id`,
					`foodsaver_id`,
					`type`,
					`infotype`
				)
				VALUES
				' . implode(',', $values) . '
		'
        );
    }

    public function getInfoFollowerIds($id)
    {
        return $this->db->fetchAllValues(
            '
			SELECT 	fs.`id`

			FROM 	`fs_fairteiler_follower` ff,
					`fs_foodsaver` fs

			WHERE 	ff.foodsaver_id = fs.id
			AND 	ff.fairteiler_id = :id
		',
            [':id' => $id]
        );
    }

    public function listFairteiler($bezirk_ids)
    {
        if (!$bezirk_ids) {
            return [];
        }
        if ($fairteiler = $this->db->fetchAll(
            '
			SELECT 	`id`,
					`name`,
					`picture`
			FROM 	`fs_fairteiler`
			WHERE 	`bezirk_id` IN( ' . implode(',', $bezirk_ids) . ' )
			AND 	`status` = 1
			ORDER BY `name`
		'
        )
        ) {
            foreach ($fairteiler as $key => $ft) {
                $fairteiler[$key]['pic'] = false;
                if (!empty($ft['picture'])) {
                    $fairteiler[$key]['pic'] = array(
                        'thumb' => 'images/' . str_replace('/', '/crop_1_60_', $ft['picture']),
                        'head' => 'images/' . str_replace('/', '/crop_0_528_', $ft['picture']),
                        'orig' => 'images/' . ($ft['picture']),
                    );
                }
            }

            return $fairteiler;
        }

        return [];
    }

    public function listFairteilerNested($bezirk_ids = [])
    {
        if (!empty($bezirk_ids) && ($fairteiler = $this->db->fetchAll(
                '
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
			ORDER BY ft.`name`
		'
            ))
        ) {
            $out = array();

            foreach ($fairteiler as $key => $ft) {
                if (!isset($out[$ft['bezirk_id']])) {
                    $out[$ft['bezirk_id']] = array(
                        'id' => $ft['bezirk_id'],
                        'name' => $ft['bezirk_name'],
                        'fairteiler' => array(),
                    );
                }
                $pic = false;
                if (!empty($ft['picture'])) {
                    $pic = array(
                        'thumb' => 'images/' . str_replace('/', '/crop_1_60_', $ft['picture']),
                        'head' => 'images/' . str_replace('/', '/crop_0_528_', $ft['picture']),
                        'orig' => 'images/' . ($ft['picture']),
                    );
                }
                $out[$ft['bezirk_id']]['fairteiler'][] = array(
                    'id' => $ft['id'],
                    'name' => $ft['name'],
                    'picture' => $ft['picture'],
                    'pic' => $pic,
                );
            }

            return $out;
        }

        return [];
    }

    public function getFairteilerIds($fsId)
    {
        return $this->db->fetchAllValues(
            'SELECT fairteiler_id FROM fs_fairteiler_follower WHERE foodsaver_id = :id',
            [':id' => $fsId]
        );
    }

    public function follow($ft_id, $fs_id, $infotype)
    {
        $this->db->insertIgnore(
            'fs_fairteiler_follower',
            [
                'fairteiler_id' => $ft_id,
                'foodsaver_id' => $fs_id,
                'type' => 1,
                'infotype' => $infotype,
            ]
        );
    }

    public function unfollow($ft_id, $fs_id)
    {
        return $this->db->delete('fs_fairteiler_follower', ['fairteiler_id' => $ft_id, 'foodsaver_id' => $fs_id]);
    }

    public function getFollower($id)
    {
        if ($follower = $this->db->fetchAll(
            '
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

		',
            [':id' => $id]
        )
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
                'all' => $all,
            );
        }

        return false;
    }

    public function acceptFairteiler($id)
    {
        $this->db->update('fs_fairteiler', ['status' => 1], ['id' => $id]);
    }

    public function updateFairteiler($id, $data)
    {
        $this->db->requireExists('fs_fairteiler', ['id' => $id]);
        $this->db->update('fs_fairteiler', $data, ['id' => $id]);

        return true;
    }

    public function deleteFairteiler($id)
    {
        $this->db->delete('fs_fairteiler_follower', ['fairteiler_id' => $id]);

        return $this->db->delete('fs_fairteiler', ['id' => $id]);
    }

    public function getFairteiler($id)
    {
        if ($ft = $this->db->fetch(
            '
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
			WHERE 	ft.id = :id
		',
            [':id' => $id]
        )
        ) {
            $ft['pic'] = false;
            if (!empty($ft['picture'])) {
                $ft['pic'] = array(
                    'thumb' => 'images/' . str_replace('/', '/crop_1_60_', $ft['picture']),
                    'head' => 'images/' . str_replace('/', '/crop_0_528_', $ft['picture']),
                    'orig' => 'images/' . ($ft['picture']),
                );
            }

            return $ft;
        }

        return false;
    }

    public function addFairteiler($fs_id, $data)
    {
        $db_data = array_merge(
            $data,
            [
                'add_date' => date('Y-m-d H:i:s'),
                'add_foodsaver' => $fs_id,
            ]
        );
        $ft_id = $this->db->insert('fs_fairteiler', $db_data);
        if ($ft_id) {
            $this->db->insert(
                'fs_fairteiler_follower',
                ['fairteiler_id' => $ft_id, 'foodsaver_id' => $fs_id, 'type' => 2]
            );
        }

        return $ft_id;
    }

    public function mayFairteiler(int $foodsaverId, int $fairteilerId): bool
    {
        $ids = $this->getFairteilerIds($foodsaverId);

        if ($ids && in_array($fairteilerId, $ids, true)) {
            return true;
        }

        return false;
    }
}
