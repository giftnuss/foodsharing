<?php

namespace Foodsharing\Modules\Activity;

use Foodsharing\Modules\Core\BaseGateway;

class ActivityGateway extends BaseGateway
{
	private const ITEMS_PER_PAGE = 10;

	public function fetchAllBasketWallUpdates($fsId, $page): array
	{
		$stm = '
			SELECT
				w.id,
				w.body,
				w.time,
				UNIX_TIMESTAMP(w.time) AS time_ts,
				fs.id AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				b.id AS basket_id	
			FROM
				fs_basket_has_wallpost hw,
				fs_foodsaver fs,
				fs_wallpost w,
				fs_basket b		
			WHERE
				w.id = hw.wallpost_id		
			AND
				w.foodsaver_id = fs.id		
			AND
				hw.basket_id = b.id				
			AND
				b.foodsaver_id = :foodsaver_id
					
			AND 
				w.foodsaver_id != :foodsaver_id_dup

			AND
				b.status = 1
			
			ORDER BY w.id DESC
		
			LIMIT :lower_limit, :upper_limit			
		';

		return $this->db->fetchAll(
			$stm,
			[
				':foodsaver_id' => (int)$fsId,
				':foodsaver_id_dup' => (int)$fsId,
				':lower_limit' => (int)$page * self::ITEMS_PER_PAGE,
				':upper_limit' => self::ITEMS_PER_PAGE,
			]
		);
	}

	public function fetchAllWallpostsFromFoodBasekts($fsId, $page): array
	{
		$stm = '
			SELECT
				w.id,
				w.body,
				w.time,
				UNIX_TIMESTAMP(w.time) AS time_ts,
				fs.id AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				b.id AS basket_id		
			FROM
				fs_basket_has_wallpost hw,
				fs_foodsaver fs,
				fs_wallpost w,
				fs_basket b,
				fs_basket_anfrage ba			
			WHERE
				w.id = hw.wallpost_id		
			AND
				w.foodsaver_id = fs.id		
			AND
				hw.basket_id = b.id
			AND
				b.status = 1			
			AND
				ba.basket_id = b.id	
			AND
				ba.status < 10				
			AND 	
				w.foodsaver_id != :foodsaver_id				
			AND 
				ba.foodsaver_id = :foodsaver_id_dup			
						
			ORDER BY w.id DESC		
			LIMIT :lower_limit, :upper_limit			
		';

		return $this->db->fetchAll(
			$stm,
			[
				':foodsaver_id' => (int)$fsId,
				':foodsaver_id_dup' => (int)$fsId,
				':lower_limit' => (int)$page * self::ITEMS_PER_PAGE,
				':upper_limit' => self::ITEMS_PER_PAGE,
			]
		);
	}

	public function fetchAllFriendWallUpdates($bids, $page): array
	{
		$stm = '
			SELECT 
				w.id,
				w.body,
				w.time,
				UNIX_TIMESTAMP(w.time) AS time_ts,
				fs.id AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo,
				
				poster.id AS poster_id,
				poster.name AS poster_name
			FROM 
				fs_foodsaver_has_wallpost hw,
				fs_foodsaver fs,
				fs_wallpost w				
			LEFT JOIN
				fs_foodsaver poster				
			ON w.foodsaver_id = poster.id				
			WHERE 
				w.id = hw.wallpost_id
			AND 
				hw.foodsaver_id = fs.id
			AND 
				hw.foodsaver_id IN(' . implode(',', $bids) . ')			
				
			ORDER BY w.id DESC		
			LIMIT :lower_limit, :upper_limit			
		';

		return $this->db->fetchAll(
			$stm,
			[':lower_limit' => (int)$page * self::ITEMS_PER_PAGE, ':upper_limit' => self::ITEMS_PER_PAGE]
		);
	}

	public function fetchAllMailboxUpdates($mb_ids, $page): array
	{
		$stm = '
				SELECT
					m.id,
					m.sender,
					m.subject,
					m.body,
					m.time,
					UNIX_TIMESTAMP(m.time) AS time_ts,
					b.name AS mb_name
			
				FROM
					fs_mailbox_message m
				LEFT JOIN
					fs_mailbox b
				ON b.id = m.mailbox_id
			
				WHERE
					m.mailbox_id IN(' . implode(',', $mb_ids) . ')
					
				ORDER BY m.id DESC		
			LIMIT :lower_limit, :upper_limit			
		';

		return $this->db->fetchAll(
			$stm,
			[':lower_limit' => (int)$page * self::ITEMS_PER_PAGE, ':upper_limit' => self::ITEMS_PER_PAGE]
		);
	}

	public function fetchAllForumUpdates($bids, $page): array
	{
		$stm = '		
			SELECT 		t.id,
						t.name,
						t.`time`,
						UNIX_TIMESTAMP(t.`time`) AS time_ts,
						fs.id AS foodsaver_id,
						fs.name AS foodsaver_name,
						fs.photo AS foodsaver_photo,
						fs.sleep_status,
						p.body AS post_body,
						p.`time` AS update_time,
						UNIX_TIMESTAMP(p.`time`) AS update_time_ts,
						t.last_post_id,
						bt.bezirk_id,
						b.name AS bezirk_name,
						bt.bot_theme
		
			FROM 		fs_theme t,
						fs_theme_post p,
						fs_bezirk_has_theme bt,
						fs_foodsaver fs,
						fs_bezirk b
		
			WHERE 		t.last_post_id = p.id 		
			AND 		p.foodsaver_id = fs.id
			AND 		bt.theme_id = t.id
			AND 		bt.bezirk_id IN(' . implode(',', $bids) . ')
			AND 		bt.bot_theme = 0
			AND 		bt.bezirk_id = b.id
			AND 		t.active = 1
		
			ORDER BY t.last_post_id DESC		
			LIMIT :lower_limit, :upper_limit			
		';

		return $this->db->fetchAll(
			$stm,
			[':lower_limit' => (int)$page * self::ITEMS_PER_PAGE, ':upper_limit' => self::ITEMS_PER_PAGE]
		);
	}

	public function fetchAllStoreUpdates($fsId, $page): array
	{
		$stm = '			
			SELECT 	n.id, n.milestone, n.`text` , n.`zeit` AS update_time, UNIX_TIMESTAMP( n.`zeit` ) AS update_time_ts, fs.name AS foodsaver_name, fs.sleep_status, fs.id AS foodsaver_id, fs.photo AS foodsaver_photo, b.id AS betrieb_id, b.name AS betrieb_name
			FROM 	fs_betrieb_notiz n, fs_foodsaver fs, fs_betrieb b, fs_betrieb_team bt
			
			WHERE 	n.foodsaver_id = fs.id
			AND 	n.betrieb_id = b.id
			AND 	bt.betrieb_id = n.betrieb_id
			AND 	bt.foodsaver_id = :foodsaver_id
			AND 	n.milestone = 0
			AND 	n.last = 1
			
			ORDER BY n.id DESC
			LIMIT :lower_limit, :upper_limit			
		';

		return $this->db->fetchAll(
			$stm,
			[
				':foodsaver_id' => (int)$fsId,
				':lower_limit' => (int)$page * self::ITEMS_PER_PAGE,
				':upper_limit' => self::ITEMS_PER_PAGE,
			]
		);
	}

	public function fetchAllBuddies($bids): array
	{
		$stm = 'SELECT photo,name,id FROM fs_foodsaver WHERE id IN(' . implode(',', $bids) . ')';

		return $this->db->fetchAll($stm);
	}
}
