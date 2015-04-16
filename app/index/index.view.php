<?php
class IndexView extends View
{
	
	public function index($first_content, $gerettet, $fairteiler, $baskets)
	{
		$ps = new vPageslider();

		$ps->addSection($first_content,array(
			'color' => '#4A3520',
			'anchor' => 'home'
		));
		
		$ps->addSection($this->countNumber(),array(
			'color' => '#ffffff',
			'anchor' => 'gerettet',
			'onload' => '$("#stat-count-number").animateNumber({ number: '.(int)$gerettet.',numberStep:$.animateNumber.numberStepFactories.separator(".") });',
			'onleave' => '$("#stat-count-number").animateNumber({ number: 0});'
		));
		
		$ps->addSection($this->howto(),array(
			'anchor' => 'howto'
		));
		
		$ps->addSection($this->map(),array(
			'anchor' => 'map',
			'color' => '#ffffff',
			'onload' => 'indexmap.init();',
			'wrapper' => false
		));
		
		
		
		$ps->addSection($this->machMit(),array(
			'anchor' => 'mach-mit'
		));
		
		return $ps->render();
	}
	
	private function map()
	{
		return '<div id="indexmap_mapview" style="width:100%;height:100%;"></div>';
	}
	
	private function machMit()
	{
		return '
		<div class="pure-g">
		    <div class="pure-u-1 pure-u-md-1-1">
				<div class="inside">
					<div style="text-align:center;">
						<h3>Mach mit!</h3>
						<p><a class="button" href="#" onclick="ajreq(\'join\',{app:\'login\'});return false;">Registrieren</a> <a class="button" href="/login">Login</a></p>
					</div>
				</div>	
			</div>
		</div>
		';
	}
	
	private function howto()
	{
		addJs('$(".vidlink").click(function(ev){
			ev.preventDefault();
			$vid = $(this);
			$vid.parent().html(\'<iframe width="420" height="315" src="\'+$vid.attr(\'href\')+\'" frameborder="0" allowfullscreen></iframe>\');
		});');
		return '
		<div class="howto">
			<div class="pure-g">
			    <div class="pure-u-1 pure-u-md-1-2">
					<div class="inside">
						<h3>Wie funktioniert&#39;s?</h3>
						<p>Was ist Foodsharing und wie funktioniert es?</p>
						<p>Was steckt hinter der Organisation und wie kannst du dich dort einbringen?</p>
						<p>Diese und weitere Fragen beantworten wir in diesem Video.</p>
					</div>
				</div>
			    <div class="pure-u-1 pure-u-md-1-2">
					<div class="inside">
						<a class="vidlink" href="https://www.youtube.com/embed/dqsVjuK3rTc?autoplay=1"><img class="corner-all" src="/img/howto.jpg" /></a>
					</div>
				</div>
			</div>
		</div>';
	}
	
	private function countNumber()
	{
		return '
		<div class="countnumber">
			<p>schon</p>
			<p class="number"><span id="stat-count-number">0</span> KG</p>
			<p>gerettet!</p>
		</div>';
	}
	
	public function foodSlider($ftposts,$baskets)
	{
		$fout = '
		<ul class="linklist fairteiler">';
		foreach ($ftposts as $p)
		{			
			$fout .= '
			<li>						
				<a href="/?page=fairteiler&sub=ft&id='.(int)$p['id'].'" class="corner-all">
					<span class="i">'.$this->imgFt($p).'</span>
					<span class="n">'.$p['ort'].' / '.$p['name'].'</span>
					<span class="t">'.niceDate($p['time_ts']).'</span>
					<span class="d">'.$p['body'].'</span>
					<span class="c"></span>
				</a>
			</li>';
		}
		
		$fout .= '
			<li><a href="/karte?load=fairteiler" class="more">'.s('more_fairteiler').'</a></li>
		</ul>';
		
		$bout = '
		<ul class="sliderlist baskets">';
		
		foreach ($baskets as $b)
		{
			$bout .= '
			<li><a href="/essenskoerbe/'.$b['id'].'" class="corner-all">'.$this->img($b).'</a></li>';
		}
		
		$bout .= '
		</ul>
		<a href="/karte?load=baskets" class="more corner-all">'.s('more_baskets').'</a>';
	
		return '';
		
		return '
		<div class="pure-g">
			<div class="pure-u-1 pure-u-md-1-1">
				<div class="inside">
					<h3>Fair-Teiler</h3>
					'.$fout.'
				</div>
			</div>
			
		</div>';
		/*
		 * <div class="pure-u-1 pure-u-md-15-24">
				<div class="inside">
					<h3>Essenskörbe</h3>
					'.$bout.'
				</div>
			</div>
		 */
	}
	
	public function fairteiler($posts)
	{
		$out = '
		<ul class="linklist fairteiler">';
		foreach ($posts as $p)
		{			
			$out .= '
			<li>						
				<a href="/?page=fairteiler&sub=ft&id='.(int)$p['id'].'" class="corner-all">
					<span class="i">'.$this->imgFt($p).'</span>
					<span class="n">'.$p['ort'].' / '.$p['name'].'</span>
					<span class="t">'.niceDate($p['time_ts']).'</span>
					<span class="d">'.$p['body'].'</span>
					<span class="c"></span>
				</a>
			</li>';
		}
		
		$out .= '
			<li><a href="/karte?load=fairteiler" class="more">'.s('more_fairteiler').'</a></li>
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
					<div class="image-content" style="background-image:url(/images/'.$n['picture'].');">
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
			<li><a href="/karte?load=baskets" class="more">'.s('more_baskets').'</a></li>
				</ul>';
		
		return v_field($out,s('new_foodbaskets'));
	}
	
	private function img($basket)
	{
		if($basket['picture'] != '' && file_exists(ROOT_DIR . 'images/basket/thumb-'.$basket['picture']))
		{
			return '<img src="/images/basket/thumb-'.$basket['picture'].'" height="75" />';
		}
		return '<img src="/img/basket75x75.png" height="75" />';
		/*
		 * $check =false;
		if($basket['picture'] != '')
		{
			if(file_exists(ROOT_DIR . 'images/basket/75x75-'.$basket['picture']))
			{
				$check = true;
			}
			else if(file_exists(ROOT_DIR . 'images/basket/'.$basket['picture']))
			{
				copy(ROOT_DIR . 'images/basket/'.$basket['picture']);
			}
		}
		if($check)
		{
			return '<img src="/images/basket/thumb-'.$basket['picture'].'" height="75" />';
		}
		return '<img src="/img/basket75x75.png" height="75" />';
		 */
	}
	
	public function berlinbadge()
	{
		return '
		<a style="width:300px;height:300px;display:block;position:absolute;top:30px;left:10px;background-image:url(/img/berlinbadge.png);background-repeat:no-repeat;z-index:500;text-decoration:none !important;" href="/event">&nbsp;</a>';
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
