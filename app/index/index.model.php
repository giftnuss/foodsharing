<?php
class IndexModel extends Model
{
	public function latestNews($count = 3)
	{
		return $this->q('
			SELECT 
				n.`id`,
				n.`name`,
				n.`teaser`,
				UNIX_TIMESTAMP(n.`time`) AS time_ts,
				n.picture,
				b.name AS bezirk_name
				
			FROM
				'.PREFIX.'blog_entry n,
				'.PREFIX.'bezirk b
				
			WHERE
				n.bezirk_id = b.id
				
			AND
				n.picture != ""
				
			ORDER BY
				n.`id` DESC
				
			LIMIT 
				'.(int)$count.'
		');
	}
	
	public function getNewestFairteilerPosts($limit = 10)
	{
		return $this->q('
			SELECT 		ft.id,
						wp.time,
						UNIX_TIMESTAMP(wp.time) AS time_ts,
						wp.body,
						wp.attach,
						ft.ort,
						ft.name
			
			FROM 		fs_fairteiler_has_wallpost hw
			LEFT JOIN 	fs_wallpost wp
			ON 			hw.wallpost_id = wp.id
		
			LEFT JOIN 	fs_fairteiler ft ON hw.fairteiler_id = ft.id
				
			ORDER BY 	wp.id DESC
				
			LIMIT 0,'.$limit.'
		
		'); // WHERE 		wp.attach != ""
	}
	
	public function getNewestFoodbaskets($limit = 10)
	{
		return $this->q('
		
			SELECT
				b.id,
				b.`time`,
				UNIX_TIMESTAMP(b.`time`) AS time_ts,
				b.description,
				b.picture,
				b.contact_type,
				b.tel,
				b.handy,
				b.fs_id AS fsf_id,
				fs.id AS fs_id,
				fs.name AS fs_name,
				fs.photo AS fs_photo
		
			FROM
				'.PREFIX.'basket b,
				'.PREFIX.'foodsaver fs
				
			WHERE
				b.status = 1
		
			ORDER BY 
				id DESC
				
			LIMIT 
				0,'.$limit.'
		
		');
	}
	
	public function closeBaskets($distance = 50)
	{
		$loc = S::getLocation();
		return $this->q('
			SELECT
				b.id,
				b.picture,
				b.description,
				b.lat,
				b.lon,
				(6371 * acos( cos( radians( '.$this->floatval($loc['lat']).' ) ) * cos( radians( b.lat ) ) * cos( radians( b.lon ) - radians( '.$this->floatval($loc['lon']).' ) ) + sin( radians( '.$this->floatval($loc['lat']).' ) ) * sin( radians( b.lat ) ) ))
				AS distance
			FROM
				fs_basket b
	
			WHERE
				b.status = 1
	
			AND
				foodsaver_id != '.(int)fsId().'
		
			HAVING
				distance <='.(int)$distance.'
	
			ORDER BY
				distance ASC
	
			LIMIT 6
		');
	}
}