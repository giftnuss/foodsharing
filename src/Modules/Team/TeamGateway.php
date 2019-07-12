<?php

namespace Foodsharing\Modules\Team;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\DBConstants\Region\RegionIDs;

class TeamGateway extends BaseGateway
{
	public function getTeam($region_id = RegionIDs::TEAM_BOARD_MEMBER): array
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
		$orgas = $this->db->fetchAll($stm, [':region_id' => $region_id]);
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
                        fs.homepage,
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

	/**
	 * Function to check and block an IP address.
	 *
	 * @param int $durationSeconds
	 * @param string $context
	 *
	 * @return bool
	 */
	public function isABlockedIP(int $durationSeconds, string $context): bool
	{
		if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = strip_tags($_SERVER['REMOTE_ADDR']);
		} else {
			$ip = strip_tags($_SERVER['HTTP_X_FORWARDED_FOR']);
		}

		$context = strip_tags($context);

		if (($block = $this->db->fetch(
				'SELECT UNIX_TIMESTAMP(`start`) AS `start`,`duration` FROM fs_ipblock WHERE ip = :ip AND context = :context',
				[[':ip' => $ip], [':context' => $context]]
			)) && time() < ((int)$block['start'] + (int)$block['duration'])) {
			return true;
		}

		$this->db->insertOrUpdate('fs_ipblock', [
			'ip' => $ip,
			'context' => $context,
			'start' => $this->db->now(),
			'duration' => $durationSeconds
		]);

		return false;
	}
}
