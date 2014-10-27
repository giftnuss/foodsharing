<?php
class IndexView extends View
{
	
	public function fairteiler($posts)
	{
		$out = '
		<ul class="linklist fairteiler">';
		foreach ($posts as $p)
		{			
			$out .= '
			<li>						
				<a href="?page=fairteiler&sub=ft&id='.(int)$p['id'].'" class="corner-all">
					<span class="i">'.$this->imgFt($p).'</span>
					<span class="n">'.$p['ort'].' / '.$p['name'].'</span>
					<span class="t">'.niceDate($p['time_ts']).'</span>
					<span class="d">'.$p['body'].'</span>
					<span class="c"></span>
				</a>
			</li>';
		}
		
		
		$out .= '
			<li><a href="?page=map&load=fairteiler" class="more">'.s('more_fairteiler').'</a></li>
		</ul>';
		return v_field($out,s('fairteiler'));
	}
	
	private function imgFt($post)
	{
		if(!empty($post['attach']))
		{
			$attach = json_decode($post['attach'],true);
			if(isset($attach['image']) && !empty($attach['image']))
			{
				$img = $attach['image'][0];
				
				if(file_exists(ROOT_DIR . 'images/wallpost/thumb_'.$img['file']))
				{
					return '<img src="/images/wallpost/thumb_' . $img['file'] . '" height="75" />';
				}
			}
		}
		
		return '<img src="/img/fairteiler75x75.png" height="75" />';
	}
	
	public function joinIndex()
	{
		
		return '
				<article>
					<div class="image-content" style="background-image:url(http://www1.wdr.de/mediathek/video/sendungen/westart/ernte108_v-ARDFotogalerie.jpg);">
						<div class="text-wrapper">
					    	<div class="text-content text-join corner-all">
								<h2>Mach mit!</h2>
								<p>und beginne schon heute, Deine überschüssigen Lebensmittel online anzubieten bzw. abzuholen. Die Registrierung ist ganz einfach und schnell.</p>
            
								<p style="text-align:center;"><a onclick="ajreq(\'join\',{app:\'login\'});return false;" href="/?page=join" class="button join">'.s('join').'</a></p>
							</div>
						</div>	
					</div>
				</article>';
	}
	
	public function newsSlider($news)
	{
		$out = array();
		if($news)
		{
			foreach ($news as $n)
			{
				$out[] = '
				<article>
					<div class="image-content" style="background-image:url(http://www.lebensmittelretten.de/freiwillige/images/'.$n['picture'].');">
						<div class="text-wrapper">
					    	<div class="text-content corner-all">
								<h2>'.$n['name'].'</h2>
								<p>'.$n['teaser'].'</p>
								<a href="/?page=blog&sub=post&id='.$n['id'].'" class="button read-more">'.s('read-more').'</a>
							</div>
						</div>	
					</div>
				</article>';
			}
		}
		
		return $out;
	}
	
	public function baskets($baskets)
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
			<li><a href="?page=map&load=baskets" class="more">'.s('more_baskets').'</a></li>
				</ul>';
		
		return v_field($out,s('new_foodbaskets'));
	}
	
	private function img($basket)
	{
		if($basket['picture'] != '' && file_exists(ROOT_DIR . 'images/basket/75x75-'.$basket['picture']))
		{
			return '<img src="/images/basket/thumb-'.$basket['picture'].'" height="75" />';
		}
		return '<img src="/img/basket75x75.png" height="75" />';
	}
	
	public function printSlider($articles = array())
	{
		addJs('
					$("#index-slider").css("visibility","visible");
		
				$("#index-slider").slippry({
			  // general elements & wrapper
			  slippryWrapper: \'<div class="sy-box news-slider" />\', // wrapper to wrap everything, including pager
			  elements: "article", // elments cointaining slide content
		
			  // options
			  adaptiveHeight: true, // height of the sliders adapts to current
			  captions: false,
		
		
		
			  // transitions
			  transition: "fade", // fade, horizontal, kenburns, false
			  speed: 1200,
			  pause: 15000,
		
			  // slideshow
			  autoDirection: "prev"
			});
		
			');
		
		$out = '';
		
		foreach ($articles as $a)
		{
			$out .= $a;
		}
		
		return '
			<section id="index-slider">
				'.$out.'
			</section>';
	}
}