<?php 
class SearchXhr extends Control
{
	
	public function __construct()
	{
		$this->model = new SearchModel();
		$this->view = new SearchView();

		parent::__construct();
	}
	
	public function search()
	{
		if(S::may('fs'))
		{
			if($res = $this->model->search($_GET['s']))
			{
				
				$out = array();
				foreach ($res as $key => $value)
				{
					if(count($value) > 0)
					{
						$out[] = array(
								'title' => s($key),
								'result' => $value
						);
					}
					
				}

				return array(
					'result' => $out
				);
			}
		}
		
		return array(
			'status' => 0
		);
	}
	
	public function betriebe()
	{
		echo '[{ "team": "Boston Celtics" },{ "team": "Dallas Mavericks" },{ "team": "Brooklyn Nets" },{ "team": "Houston Rockets" },{ "team": "New York Knicks" },{ "team": "Memphis Grizzlies" },{ "team": "Philadelphia 76ers" },{ "team": "New Orleans Hornets" },{ "team": "Toronto Raptors" },{ "team": "San Antonio Spurs" },{ "team": "Chicago Bulls" },{ "team": "Denver Nuggets" },{ "team": "Cleveland Cavaliers" },{ "team": "Minnesota Timberwolves" },{ "team": "Detroit Pistons" },{ "team": "Portland Trail Blazers" },{ "team": "Indiana Pacers" },{ "team": "Oklahoma City Thunder" },{ "team": "Milwaukee Bucks" },{ "team": "Utah Jazz" },{ "team": "Atlanta Hawks" },{ "team": "Golden State Warriors" },{ "team": "Charlotte Bobcats" },{ "team": "Los Angeles Clippers" },{ "team": "Miami Heat" },{ "team": "Los Angeles Lakers" },{ "team": "Orlando Magic" },{ "team": "Phoenix Suns" },{ "team": "Washington Wizards" },{ "team": "Sacramento Kings" }]';
		exit(0);
	}
	
	public function people()
	{
		echo '[{ "team": "New Jersey Devils" },{ "team": "New York Islanders" },{ "team": "New York Rangers" },{ "team": "Philadelphia Flyers" },{ "team": "Pittsburgh Penguins" },{ "team": "Chicago Blackhawks" },{ "team": "Columbus Blue Jackets" },{ "team": "Detroit Red Wings" },{ "team": "Nashville Predators" },{ "team": "St. Louis Blues" },{ "team": "Boston Bruins" },{ "team": "Buffalo Sabres" },{ "team": "Montreal Canadiens" },{ "team": "Ottawa Senators" },{ "team": "Toronto Maple Leafs" },{ "team": "Calgary Flames" },{ "team": "Colorado Avalanche" },{ "team": "Edmonton Oilers" },{ "team": "Minnesota Wild" },{ "team": "Vancouver Canucks" },{ "team": "Carolina Hurricanes" },{ "team": "Florida Panthers" },{ "team": "Tampa Bay Lightning" },{ "team": "Washington Capitals" },{ "team": "Winnipeg Jets" },{ "team": "Anaheim Ducks" },{ "team": "Dallas Stars" },{ "team": "Los Angeles Kings" },{ "team": "Phoenix Coyotes" },{ "team": "San Jose Sharks" }]';
		exit(0);
	}
}