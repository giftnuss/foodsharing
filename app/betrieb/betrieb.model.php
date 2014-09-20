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
	
	public function deldate($bid,$date)
	{
		$this->del('DELETE FROM `'.PREFIX.'abholer` WHERE `betrieb_id` = '.(int)$bid.' AND `date` = '.$this->dateval($date));
		return $this->del('DELETE FROM `'.PREFIX.'fetchdate` WHERE `betrieb_id` = '.(int)$bid.' AND `time` = '.$this->dateval($date));
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