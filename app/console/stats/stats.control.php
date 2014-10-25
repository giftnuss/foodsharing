<?php
class StatsControl extends ConsoleControl
{	
	private $model;
	
	public function __construct()
	{		
		$this->model = new StatsModel();
	}
	
	public function betriebe()
	{
		info('Statistik Auswertung für Betriebe');
		
		$betriebe = $this->model->getBetriebe();
		
		$count = count($betriebe);
		$start_ts = time();
		
		foreach($betriebe as $i => $b)
		{
			info('calc '.$b['name'].' ('.($i+1).'/'.$count.')');
			$this->calcBetrieb($b);
			success('OK');
			info($this->calcDuration($start_ts, ($i+1), $count));
		}
		
		success('ready :o)');
	}
	
	private function calcBetrieb($betrieb)
	{
		$bid = $betrieb['id'];
		
		if($bid > 0)
		{
			$added = $betrieb['added'];
				
			if($team = $this->model->getBetriebTeam($bid))
			{
		
				foreach ($team as $fs)
				{
					$newdata = array(
							'stat_first_fetch' => $fs['stat_first_fetch'],
							'stat_add_date' => $fs['stat_add_date'],
							'foodsaver_id' => $fs['foodsaver_id'],
							'betrieb_id' => $bid,
							'verantwortlich' => $fs['verantwortlich'],
							'stat_fetchcount' => $fs['stat_fetchcount'],
							'stat_last_fetch' => $fs['stat_last_fetch']
					);
						
					/* first_fetch */
					if($first_fetch = $this->model->getFirstFetchInBetrieb($bid,$fs['foodsaver_id']))
					{
						if((int)$fs['first_fetch_ts'] == 0)
						{
							$newdata['stat_first_fetch'] = $first_fetch;
						}
						if((int)$fs['add_date_ts'] == 0)
						{
							$newdata['stat_add_date'] = $first_fetch;
						}
					}
						
					/*last fetch*/
					if($last_fetch = $this->model->getLastFetchInBetrieb($bid, $fs['foodsaver_id']))
					{
						$newdata['stat_last_fetch'] = $last_fetch;
					}
						
					/* add date*/
					if((int)$newdata['stat_add_date'] == 0)
					{
						$newdata['stat_add_date'] = $added;
					}
						
					/*fetchcount*/
					$fetchcount = $this->model->getBetriebFetchCount($bid,$fs['foodsaver_id'],$fs['stat_last_update'],$fs['stat_fetchcount']);
						
					$this->model->updateBetriebStat(
							$bid, // betrieb id
							$fs['foodsaver_id'], // foodsaver_id
							$newdata['stat_add_date'], // add date
							$newdata['stat_first_fetch'], // erste mal abholen
							$fetchcount, // anzahl abholungen
							$newdata['stat_last_fetch']
					);
				}
			}
		}
	}
	
	/**
	 * public accacable method to calculate all statictic for each bezirk
	 * for the moment i have no other idea to calculate live because the hierarchical child bezirk query neeed so long time
	 * 
	 */
	public function bezirke()
	{
		info('Statistik Auswertung für Bezirke');
		
		// get all Bezirke non memcached
		$bezirke = $this->model->getAllBezirke();
		
		$start_ts = time();
		
		$count = count($bezirke);
		foreach ($bezirke as $i => $b)
		{
			info($b['id'].' '.$b['name'].' ('.($i+1).'/'.$count.')');
			
			$kilo = $this->calcBezirk($b);
			
			success($kilo.' KG fetched :) ');
			
			info($this->calcDuration($start_ts,($i+1),$count));
			
		}
		
		success('ready :o)');
	}
	
	private function calcBezirk($bezirk)
	{
		$bezirk_id = $bezirk['id'];
		$last_update = $bezirk['stat_last_update'];
		
		$child_ids = $this->model->getChildBezirke($bezirk_id);
		
		/* abholmenge & anzahl abholungen */
		$stat_fetchweight = $this->model->getFetchWeight($bezirk_id,$last_update,$child_ids);
		$stat_fetchcount = $stat_fetchweight['count'];
		$stat_fetchweight = $stat_fetchweight['weight'];
		
		/* anzahl foodsaver */
		$stat_fscount = $this->model->getFsCount($bezirk_id,$child_ids);
		
		/*anzahl botschafter*/
		$stat_botcount = $this->model->getBotCount($bezirk_id,$child_ids);
		
		/* anzahl posts */
		$stat_postcount = $this->model->getPostCount($bezirk_id,$child_ids);
		
		/* fairteiler_count */
		$stat_fairteilercount = $this->model->getFairteilerCount($bezirk_id,$child_ids);
		
		/* count betriebe */
		$stat_betriebecount = $this->model->getBetriebCount($bezirk_id,$child_ids);
		
		/* count koorp betriebe */
		$stat_betriebCoorpCount = $this->model->getBetriebKoorpCount($bezirk_id,$child_ids);
		
		$this->model->updateStats($bezirk_id, $stat_fetchweight, $stat_fetchcount, $stat_postcount, $stat_betriebecount, $stat_betriebCoorpCount, $stat_botcount, $stat_fscount, $stat_fairteilercount);
		
		return $stat_fetchweight;
	}
}
