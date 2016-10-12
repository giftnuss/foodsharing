<?php
class IndexModel extends Model
{
	public function getGerettet()
	{
		return $this->qOne('SELECT stat_fetchweight FROM '.PREFIX.'bezirk WHERE id = 741');
	}
	
	public function closeBaskets($distance = 50,$loc = false)
	{
		if($loc === false)
		{
			$loc = S::getLocation();
		}
		
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
