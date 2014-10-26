<?php
class IndexView extends View
{
	
	public function fairteiler()
	{
		return v_field('',s('fairteiler'));
	}
	
	public function baskets()
	{
		return v_field('',s('foodbaskets'));
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