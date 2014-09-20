<?php
class TemplatesModel extends Model
{
	public function getTemplates()
	{
		$templates = array(
			array(
				'title' => 'Leichte Überschrift',
				'description' => 'Blasse Überschrift mit gespricheltem hintergrund',
				'content' => '
				<div class="main-title-1 custom-font-1"><span>Leichte Überschrift</span></div>
				<p>hier gehts weiter...</p>'
			),
			array(
				'title' => 'Dicke Überschrift',
				'description' => '',
				'content' => '
				<h3>Ich bin eine dicke Überschrift</h3>
				<p>hier gehts weiter...</p>'
			),
			array(
				'title' => 'Fetter Link Button',
				'description' => '',
				'content' => '
				<a class="btn-1 btn-align-left" href="#" target="_blank"><span>Fetter Link</span></a>
				<p>hier gehts weiter...</p>'
			),
			array(
				'title' => 'Spacer',
				'description' => 'Horizontale Linie um Inhalt abzutrennen',
				'content' => '
				<div class="shortcode-spacer-1">&nbsp;</div>
				<p>hier gehts weiter...</p>'
			),
			array(
				'title' => 'Liste mit gelben Sternchen',
				'description' => 'Aufzählungsliste, die Sternchen sind eventuell erst nach dem einfügen zu sehen',
				'content' => '
				<ul>
					<li>Mauris elit erat, laoreet ac posuere eget blandit</li>
					<li>Vestibulum vitae justo nisi, nec pharetra ipsum</li>
					<li>Donec ornare dapibus ante ut porttitor nam sit</li>
					<li>Aliquam vestibulum condimentum leo, vel porta</li>
				</ul>'
			),
			array(
				'title' => 'Zitat',
				'description' => 'mit dickem Anführungszeichen an der linken Seite',
				'content' => '
				<div class="blockquote-quote-marks custom-font-1">
					<blockquote>
						Nam sed enim ac diam condime ntum blandit, nullam mollis justo.
					</blockquote>
				</div>
				<p>hier gehts weiter...</p>'
			),
			array(
				'title' => 'Zitat 2',
				'description' => 'mit Stern an der linken Seite',
				'content' => '
				<div class="blockquote-star custom-font-1">
					<blockquote>
						Nam sed enim ac diam condime ntum blandit, nullam mollis justo.
					</blockquote>
				</div>
				<p>hier gehts weiter...</p>'
			),
			array(
				'title' => 'Hervorgehobener Text Block',
				'description' => 'mit sehr fettem Text',
				'content' => '
				<div class="blockquote-box custom-font-1">
					<blockquote>
						Nam sed enim ac diam condime ntum blandit, nullam mollis justo.
					</blockquote>
				</div>
				<p>hier gehts weiter...</p>'
			),
			array(
				'title' => 'Erfolgsmeldung',
				'description' => '',
				'content' => '
				<div class="success custom-font-1">
					<div>
						<p><span>Prima!</span></p>
						<p>Das hat geklappt!</p>
					</div>
				</div>
				<p>hier gehts weiter...</p>'
			),
			array(
				'title' => 'Fehlermeldung',
				'description' => '',
				'content' => '
				<div class="error custom-font-1">
					<div>
						<p><span>Oooops!</span></p>
						<p>Da ging was in die Hose!</p>
					</div>
				</div>
				<p>hier gehts weiter...</p>'
			),
			array(
				'title' => 'Partner Eintrag',
				'description' => 'Partner Einträge für die öffentliche Seite',
				'content' => '
				<div class="partner">
					<img class="logo" src="/img/logo_dummy.png" alt="Logo" />
					<h3>Partner-Name</h3>
					<p>
						<strong>Homepage:</strong><br>
						<a href="http://www.lebensmittelretten.de/" target="_blank">www.homepage.de</a>					
					</p>
					<div class="clear"></div>
					<p>
						Dies ist ein Typoblindtext. An ihm kann man sehen, ob alle Buchstaben da sind und wie sie aussehen. Manchmal benutzt man Worte wie Hamburgefonts, Rafgenduks oder Handgloves, um Schriften zu testen. Manchmal Sätze, die alle Buchstaben des Alphabets enthalten - man nennt diese Sätze »Pangrams«. Sehr bekannt ist dieser: The quick brown fox jumps over the lazy old dog. 
					</p>
				</div>
				<div class="shortcode-spacer-1">&nbsp;</div>
				<p>hier gehts weiter...</p>'
			)
		);
		return $templates;
	}
}