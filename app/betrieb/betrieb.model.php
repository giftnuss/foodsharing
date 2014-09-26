<?php
class BetriebModel extends Model
{
	public function addFetchDate($bid,$time,$fetchercount)
	{
		return $this->insert('
			INSERT INTO `'.PREFIX.'fetchdate`
			(
				`betrieb_id`, 
				`time`, 
				`fetchercount`
			) 
			VALUES 
			(
				'.(int)$bid.',
				'.$this->dateval($time).',
				'.(int)$fetchercount.'
			)
		');
	}
	
	public function getFetchHistory($betrieb_id,$from,$to)
	{
		return $this->q('
			SELECT
				fs.id,
				fs.name,
				fs.nachname,
				fs.photo,
				a.date,
				UNIX_TIMESTAMP(a.date) AS date_ts
	
			FROM
				'.PREFIX.'foodsaver fs,
				'.PREFIX.'abholer a
	
			WHERE
				a.foodsaver_id = fs.id
	
			AND
				a.betrieb_id = '.(int)$betrieb_id.'
	
			AND
				a.date >= '.$this->dateVal($from).'
	
			AND
				a.date <= '.$this->dateVal($to).'
	
			ORDER BY
				a.date
	
		');
	}
	
	public function deldate($bid,$date)
	{
		$this->del('DELETE FROM `'.PREFIX.'abholer` WHERE `betrieb_id` = '.(int)$bid.' AND `date` = '.$this->dateval($date));
		return $this->del('DELETE FROM `'.PREFIX.'fetchdate` WHERE `betrieb_id` = '.(int)$bid.' AND `time` = '.$this->dateval($date));
	}
	
	public function listMyBetriebe()
	{
		return $this->q('
			SELECT 	b.id,
					b.name,
					b.plz,
					b.stadt,
					b.str,
					b.hsnr

			FROM
				'.PREFIX.'betrieb b,
				'.PREFIX.'betrieb_team t
				
			WHERE
				b.id = t.betrieb_id
				
			AND
				t.foodsaver_id = '.fsId().'
				
			AND
				t.active = 1
		');
	}
	
	public function listUpcommingFetchDates($bid)
	{
		if($dates = $this->q('
			SELECT 	`time`,
					UNIX_TIMESTAMP(`time`) AS `time_ts`,
					`fetchercount`
			FROM 	'.PREFIX.'fetchdate
			WHERE 	`betrieb_id` = '.(int)$bid.'
			AND 	`time` > NOW()
		'))
		{
			$out = array();
			foreach($dates as $d)
			{
				$out[date('Y-m-d H-i',$d['time_ts'])] = array(
					'time' => date('H:i:s',$d['time_ts']),
					'fetcher' => $d['fetchercount'],
					'additional' => true,
					'datetime' => $d['time']
				);
			}
			
			return $out;
		}
		
		return false;
	}
}