<footer>
	<div class="wrapper">
		<div class="pure-g">
		    <div class="pure-u-1 pure-u-md-4-24">
		    	<div class="inside">
		    		<ul class="linklist">
		    			<li><a class="corner-all" href="/team">Team</a></li>
		    			<li><a class="corner-all" href="/impressum">Impressum</a></li>
		    			<li><a class="corner-all" href="/ratgeber">Ratgeber</a></li>
						<li><a class="corner-all" href="/fuer_unternehmen">Unternehmen</a></li>
		    		</ul>
		   		</div> 
		    </div>
		    <div class="pure-u-1 pure-u-md-4-24">
		    	<div class="inside">
		    		<ul class="linklist">
			    		<li><a class="corner-all" href="/partner">Partner</a></li>
						<li><a class="corner-all" href="/statistik">Statistik</a></li>
						<li><a class="corner-all" href="/faq">F.A.Q.</a></li>
						<li><a class="corner-all" href="/unterstuetzung">Unterstützung</a></li>
					</ul>
				</div>
		    </div>
		    <div class="pure-u-1 pure-u-md-16-24">
		    	<div class="inside">
					<div class="pure-g">
<?php
		if(strpos($_SERVER['REQUEST_URI'], 'myfoodsharing.at')) {
?>
						<div class="pure-u-1 pure-u-md-1-3">
							<a class="corner-all imglink" href="http://www.lebensministerium.at/" target="_blank"><img src="/img/wien-bmlfuw.png" alt="Lebensministerium" /></a>
						</div>
						<div class="pure-u-1 pure-u-md-1-3">
							<a class="corner-all imglink" href="https://www.wienertafel.at/" target="_blank"><img src="/img/wien_tafel.png" alt="Wiener Tafel" /></a>
						</div>
						<div class="pure-u-1 pure-u-md-1-3">
<?php
			} else {
?>
						<div class="pure-u-1">
<?php
			}
?>

							<h6>Vielen Dank an unseren Öko-Hoster</h6>
							<a class="corner-all imglink" href="https://www.manitu.de/" target="_blank"><img src="/img/manitu_logo.png" alt="Manitu" /></a>
						</div>
					</div>
		    	</div>
		    </div>
		</div>
	</div>
</footer>
