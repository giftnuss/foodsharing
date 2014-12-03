<?php
class DashboardView extends View
{
	public function newBaskets($baskets)
	{
		$out = '<ul class="linklist baskets">';
		foreach ($baskets as $b)
		{
			$out .= '
			<li>
				<a onclick="ajreq(\'bubble\',{app:\'basket\',id:'.(int)$b['id'].'});return false;" href="#" class="corner-all">
					<span class="i">'.$this->img($b).'</span>
					<span class="n">Essenkorb von '.$b['fs_name'].'</span>
					<span class="t">veröffentlicht am '.niceDate($b['time_ts']).'</span>
					<span class="d">'.$b['description'].'</span>
					<span class="c"></span>
				</a>
	
			</li>';
		}
	
	
		$out .= '
				</ul>';
	
		return v_field($out,s('new_foodbaskets'));
	}
	
	public function foodsharerMenu()
	{
		return $this->menu(array(
			array('name'=> s('new_basket'),'click' => "ajreq('newbasket',{app:'basket'});return false;"),
			array('name' => s('all_baskets'),'href'=> '/karte?load=baskets')
		));
	}
	
	public function closeBaskets($baskets)
	{
		$out = '<ul class="linklist baskets">';
		foreach ($baskets as $b)
		{
			$out .= '
			<li>
				<a onclick="ajreq(\'bubble\',{app:\'basket\',id:'.(int)$b['id'].'});return false;" href="#" class="corner-all">
					<span class="i">'.$this->img($b).'</span>
					<span class="n">Essenkorb von '.$b['fs_name'].' ('.$this->distance($b['distance']).')</span>
					<span class="t">'.niceDate($b['time_ts']).'</span>
					<span class="d">'.$b['description'].'</span>
					<span class="c"></span>
				</a>
	
			</li>';
		}
	
	
		$out .= '
				</ul>';
	
		return v_field($out,s('close_foodbaskets'));
	}
	
	private function img($basket)
	{
		if($basket['picture'] != '' && file_exists(ROOT_DIR . 'images/basket/50x50-'.$basket['picture']))
		{
			return '<img src="/images/basket/thumb-'.$basket['picture'].'" height="50" />';
		}
		return '<img src="/img/basket50x50.png" height="50" />';
	}
}