<?php

namespace Foodsharing\Modules\Team;

use Foodsharing\Modules\Core\BaseGateway;

class TeamGateway extends BaseGateway
{
	public function getTeam($bezirkId = 1373): array
	{
		$out = array();
		$stm = '
				SELECT 
					fs.id, 
					CONCAT(mb.name,"@' . PLATFORM_MAILBOX_HOST . '") AS email, 
					fs.name,
					fs.nachname,
					fs.photo,
					fs.about_me_public AS `desc`,
					fs.rolle,
					fs.geschlecht,
					fs.homepage,
					fs.github,
					fs.tox,
					fs.twitter,
					fs.position,
					fs.contact_public				
				FROM 
					fs_foodsaver_has_bezirk hb

				LEFT JOIN
					fs_foodsaver fs
				ON
					hb.foodsaver_id = fs.id
				
				LEFT JOIN
					fs_mailbox mb 
				ON 
					fs.mailbox_id = mb.id
				WHERE 
					hb.bezirk_id = :region_id
				ORDER BY fs.name
		';
		$orgas = $this->db->fetchAll($stm, [':region_id' => $bezirkId]);
		foreach ($orgas as $o) {
			$out[(int)$o['id']] = $o;
		}

		return $out;
	}

	public function getUser($id)
	{
		$stm = '
                    SELECT
                        fs.id,
				CONCAT(fs.name, " ", fs.nachname) AS name,
                        fs.about_me_public AS `desc`,
                        fs.rolle,
                        fs.geschlecht,
                        fs.photo,
                        fs.twitter,
                        fs.tox,
                        fs.homepage,
                        fs.github,
                        fs.position,
                        fs.email,
                        fs.contact_public
                    FROM
                        fs_foodsaver_has_bezirk fb
                    INNER JOIN fs_foodsaver fs ON
                        fb.foodsaver_id = fs.id
                    WHERE
                        fb.foodsaver_id = :id AND(
                            fb.bezirk_id = 1564 OR fb.bezirk_id = 1565 OR fb.bezirk_id = 1373
                        )
                    LIMIT 1
		';
		if ($user = $this->db->fetch($stm, [':id' => (int)$id])
		) {
			return $user;
		}
	}
}
