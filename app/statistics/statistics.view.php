<?php
class StatisticsView extends View
{
	public function getStatCities($cities)
	{
		$out = '
		<div class="main-title-1 custom-font-1">
			<span>aktivste Städte</span>
		</div>
		<table class="stat_cities">
		';
	
		if(is_array($cities))
		{
			$i = 0;
			foreach($cities as $c)
			{
				$i++;
				$out .= '
				<tr>
					<td style="width:5px;text-align:right;padding-right:5px;" valign="top">
						<h4>'.$i.'.</h4>
					</td>
					<td class="city">
						<h4>'.$c['name'].'</h4>
						<p>'.str_replace(',00', '', number_format($c['fetchweight'], 2, ',', '.')).' KG ('.$c['percent'].'%)</p>
						<div class="percentbar">
							<div class="inner" style="width:'.$c['percent'].'%;"></div>
						</div>
					</td>
				</tr>';
			}
		}
		$out .= '
		</table>';
	
		return $out;
	}
	
	public function getStatGesamt($stat)
	{
		/*
		 *  fetchweight,
		fetchcount,
		postcount,
		betriebcount,
		korpcount,
		botcount,
		fscount,
		fairteilercount
		*/
		return '
		<div class="stat_item left">
				<div class="stat_badge">
					<div class="stat_icon fetchweight">
	
					</div>
				</div>
				<div class="stat_text">
					<h4>'.str_replace(',00', '', number_format($stat['fetchweight'], 2, ',', '.')).' KG</h4>
					<p>Lebensmittel erfolgreich vor der Tonne gerettet.</p>
				</div>
		</div>
		<div class="stat_item">
				<div class="stat_badge">
					<div class="stat_icon fetchcount">
	
					</div>
				</div>
				<div class="stat_text">
					<h4>'.number_format($stat['fetchcount'], 0, ',', '.').'</h4>
					<p>Rettungs-Einsätze haben unsere Foodsaver gemeistert.</p>
				</div>
		</div>
		<div class="stat_item left">
				<div class="stat_badge">
					<div class="stat_icon korpcount">
	
					</div>
				</div>
				<div class="stat_text">
					<h4>'.number_format($stat['korpcount'], 0, ',', '.').'</h4>
					<p>Betriebe koorperieren kontinuierlich und zufrieden mit uns.</p>
				</div>
		</div>
		<div class="stat_item">
				<div class="stat_badge">
					<div class="stat_icon fscount">
	
					</div>
				</div>
				<div class="stat_text">
					<h4>'.number_format($stat['fscount'], 0, ',', '.').'</h4>
					<p>Foosaver engagieren sich ehrenamtlich für eine Welt ohne Verschwendung von Lebensmitteln</p>
				</div>
		</div>
		<div style="clear:both;"></div>';
	}
	
	public function getStatFoodsaver($foodsaver)
	{
		$out = '
		<div style="height:45px"></div>
		<div class="main-title-1 custom-font-1">
			<span>Foodsaver</span>
		</div>
		<table class="stat_cities">
		';
	
		if(is_array($foodsaver))
		{
			$i = 0;
			foreach($foodsaver as $fs)
			{
				$i++;
				$out .= '
				<tr>
					<td style="width:5px;text-align:right;padding-right:5px;" valign="top">
						<h4>'.$i.'.</h4>
					</td>
					<td class="city">
						<h4>'.$fs['name'].' '.$fs['nachname'].'</h4>
						<p>'.str_replace(',00', '', number_format($fs['fetchweight'], 2, ',', '.')).' KG</p>
						<p>'.number_format($fs['fetchcount'], 0, ',', '.').'x abgeholt</p>
					</td>
				</tr>';
			}
		}
		$out .= '
		</table>';
	
		return $out;
	}
}