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
}