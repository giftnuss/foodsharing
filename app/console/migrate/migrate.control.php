<?php

class MigrateControl extends ConsoleControl
{		
	private $model;
	private $smtp;
	
	public function __construct()
	{
		$this->model = new MigrateModel();
		$this->smtp = false;
	}
	
	public function tmpmail()
	{
		if($users = $this->model->q('SELECT id,email FROM users WHERE deleted = 0'))
		{
			if($send = $this->model->q('SELECT user_id, email FROM mailed'))
			{
				$users = $this->filterSended($users,$send);
			}
			
			$bar = $this->progressbar(count($users));
			
			/*
			 * Make Email
			 */
			
			$email = new fEmail();
			$email->setFromEmail('info@foodsharing.de','foodsharing');
			
			
			
			$email->setSubject('Newsletter Fusion foodsharing & lebensmittelretten.de');
			
			$this->smtpReconnect();
			
			/*
			 * Make email end
			 */
			
			info('noch ' . count($users).' zu senden');
			
			$count = 0;
			foreach ($users as $i => $u)
			{
				if(!validEmail($u['email']))
				{
					continue;
				}
				$count++;
				$body = $this->getNewsletter($u['email']);
		
				$email->setBody(trim(strip_tags($body)));
				$email->setHTMLBody($body);
				
				if($this->sendNewsletter($u['email'],$email))
				{
					$this->model->insert('INSERT INTO `mailed`(`user_id`, `email`, `time`) VALUES ('.$u['id'].','.$this->model->strval($u['email']).',NOW())');
				}
				
				$bar->update($i);
				
				if($count == 500)
				{
					$count = 0;
					$this->smtpReconnect();
				}
				
			}
		}
	}
	
	private function getNewsletter($email)
	{
		return '<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8">
<style type="text/css">
	body, span, p,h1, h2,h3,li
	{
		font-family:Helvetica,Arial,"lucida grande",tahoma,verdana,arial,sans-serif;
		font-size:14px;
		color:#4A3520;
	}
	h1
	{
		font-size:17px;
		margin-bottom:25px;			
	}
	.c4,.c7
	{
		margin-top:10px;
		font-weight:bold;
	}

	.wrapper, footer
	{
		width:100%;
		padding:15px;
		max-width:900px;
		margin:0 auto;
		padding-top:25px;
	}
	.wrapper
	{
		border-radius:6px;	
		background-color:#ffffff;		
	}
	footer
	{
		font-size:12px;
	}
	p
	{
		line-height:20px;		
	}
	body
	{
		background-color:#F1E7C9;		
	}
	a{color:#46891b;text-decoration:none;} a:hover{text-decoration:underline;}
</style>
</head>
<body>
<div class="wrapper">
		
<p style="margin-bottom:0px;margin-top:-10px;">
	<a style="text-align:right;display:block;color:#FAF7E5;text-decoration:none;" href="http://countdown.lebensmittelretten.de/" target="_blank">
		<span style="margin-left:0px;font-size:20px;font-family:Arial Black, Arial;font-weight:bold;color:#4A3520;letter-spacing:-1px;">food</span><span style="margin-right:10px;font-size:20px;font-family:Arial Black, Arial;font-weight:bold;color:#4D971E;letter-spacing:-1px">sharing</span>
	</a>			
</p>
<p class="c1">
	<h1>Liebe foodsharing Begeisterte, Fans,
		LebensmittelretterInnen und engagierte Menschen für mehr Wertschätzung
		von Lebensmitteln,</h1>
</p>
<p class="c1 c3">
	<span class="c4"></span>
</p>
<p class="c1">
	<span class="c4">*** Wichtige Themen***</span>
</p>
<ul class="c14 lst-kix_jhsnnuyo07ei-0 start">
	<li class="c1 c11 c9"><span class="c5">foodsharing Webseiten
			(D, AT, CH) Wartungsarbeiten für die Fusion am 11.12.2014</span></li>
	<li class="c1 c11 c9"><span class="c5">Kennst Du eineN
			VersicherungsrechtlerIn? -&gt; Schreib an </span><span class="c5">orgateam@lebensmittelretten.de</span><span
		class="c5">&nbsp;:-)</span></li>
	<li class="c1 c9 c11"><span class="c5">Wir suchen eineN </span><span
		class="c5"><a class="c6"
			href="http://www.lebensmittelretten.de/?page=blog&sub=read&id=177">EhrenamtlicheN
				GeschäftsführerIn</a></span><span class="c5">&nbsp;für die Verwaltung
			des foodsharing e.</span><span class="c5">V. </span><span class="c5">mit
			Sitz</span><span class="c5">&nbsp;Köln</span><span class="c5">&nbsp;(Du
			solltest also aus der Nähe kommen</span><span class="c5">)</span></li>
</ul>
<p class="c1 c9" style="text-align:right:padding:0 10px;">
	<span class="c5 c8"><a href="#english">⭩ Englisch Version below</a></span>
</p>
<p class="c1">
	<span class="c4">Inhalt:</span>
</p>
<p class="c1">
	<span class="c4">1. foodsharing Fusion und </span><span class="c7">Kundgebung
		auf der Domplatte</span><span class="c4">&nbsp;in Köln zum
		zweijährigen Jubiläum von foodsharing</span>
</p>
<p class="c1">
	<span class="c4">2. foodsharing gewinnt Engagement Wettwerb </span>
</p>
<p class="c1">
	<span class="c4">3. Internationales großes foodsharing Treffen
		im Frühjahr in Berlin</span>
</p>
<p class="c1">
	<span class="c4">4. ÜbersetzerInnen &amp;
		VersicherungsrechtlerIn gesucht</span>
</p>
<p class="c1">
	<span class="c4">5. foodsharing walk</span>
</p>
<p class="c1">
	<span class="c4">6. Für Award gesucht: Lebensmittelbetriebe, die
		sich gegen Verschwendung einsetzen</span>
</p>
<p class="c1">
	<span class="c4">7</span><span class="c4">. Wegen der Fusion ist
		foodsharing.de ab Donnerstag Abend für kurze Zeit nicht erreichbar</span><span
		class="c4">&nbsp;</span>
</p>
<p class="c1 c3">
	<span class="c7"></span>
</p>
<p class="c1">
	<span class="c7">2 Jahre foodsharing: Zehntausende Foodsharer
		konnten bereits über 1 Million Kilogramm Lebensmittel retten</span><span><br></span>
</p>
<p class="c1">
	<span>Die Bewegung \'foodsharing\' feiert am 12.12.2014 zusammen
		mit Euch, den </span><span class="c7">60.000 NutzerInnen</span><span>,
		die sich gemeinsam für mehr Wertschätzung von Lebensmitteln einsetzen,
		ihr 2-jähriges Jubiläum. Nach mehr als </span><span class="c7">20 Mio.
		Seitenaufrufen</span><span>&nbsp;bekommt foodsharing endlich eine
		komplett überarbeitete neue Webseite und wird mit der
		Freiwilligen-Plattform www.lebensmittelretten.de unter den foodsharing
		Webseiten (D, AT, CH) zusammengeführt. Neben einer neuen, schnellen
		und effektiven Essenkorbfunktion ist es dann auch allen möglich, durch
		ein Upgrade zum Foodsaver (LebensmittelretterIn) zu werden. Bereits
		heute organisieren sich mehr als 9.000 ehrenamtliche Foodsaver, die
		dank der über tausend Kooperationen mit Supermärkten, Bäckereien und
		anderen Betrieben schon Lebensmittel im </span><span class="c7">Warenwert
		von ca. 3 Mio. Euro gerettet </span><span>und kostenlos verteilt
		haben. Eine Vielzahl der geretteten Lebensmittel wird in so genannten
		Fair-Teilern, Regalen oder Kühlschränken an öffentlichen Orten für
		jeden kostenlos zugänglich gemacht.</span>
</p>
<p style="text-align:center;">
	<img src="http://www.lebensmittelretten.de/images/picture/crop_0_528_546e682048a5c.jpg" />
</p>
<p class="c1">
	<span><br></span><span>Eine Besonderheit der
		foodsharing-Bewegung ist, dass sich die Menschen neben dem
		Essenüberschüsse teilen/abholen auch </span><span class="c7">unentgeldlich
		ehrenamtlich</span><span>&nbsp;engagieren, um weitere
		Aufgabenbereiche, von der Programmierung über das Design bis hin zur
		Öffentlichkeitsarbeit und alle Aufgaben des Organisationsteams, zu
		bewerkstelligen. Hinzu kommt die kostenlose Unterstützung von
		verschiedenen DienstleisterInnen, wie Druckereien, RechtsberaterInnen,
		Server-AnbieterInnen u.v.m., die es möglich machen, </span><span class="c7">dass
		foodsharing auch in Zukunft kostenlos und werbefrei funktioniert.</span>
</p>
<p class="c1 c3">
	<span class="c7"></span>
</p>
<p class="c1">
	<span class="c5">Ganz besonders möchten wir an der Stelle
		unserem so fleißigen und wunderbaren IT-Team um den geldfrei lebenden
	</span><span class="c10 c5"><a class="c6"
		href="http://geldfrei.net/">Raphael
			Wintrich</a></span> bedanken (Kristijan
		Miklobusec, Matthias Larisch, Nils Richter und André Piotrowski im IT-Support). Außerdem dem Grafik-Team für die genialen neuen
		Flyer und Plakate, den Beteiligten des neuen <span class="c10 c5"><a
		class="c6" href="http://youtu.be/dqsVjuK3rTc">Erklärvideo von
			foodsharing</a></span><span class="c5">&nbsp;sowie allen anderen Menschen
		die in einen der Gruppen aktiv sind. Selbsverständlich aber auch allen
		Betriebsverantworltichen, BotschafterInnen und dem gesamten Orgateam
		für Euren so bedingungslosen Einsatz gegen die Verschwendung und für
		mehr Wertschätzung und Bewusstsein. Dank Euch allen für Euer Wirken,
		Eure Energie und Freude mit der Ihr Euch einbringt - Ihr macht
		foodsharing möglich!</span>
</p>
<p class="c1 c3">
	<span></span>
</p>
<p class="c1">
	<span>Nach dem Motto: \'</span><span class="c7">global denken und
		lokal handeln</span><span>\', ist foodsharing dank Deines Einsatzes und
		zehntausender anderer motivierter Menschen binnen kürzester Zeit zu
		einer der am schnellsten wachsenden sozialen Bewegung im
		deutschsprachigen Raum geworden. Ziel ist es, die Webseite weiter
		auszubauen und damit der Kultur des Teilens ein unkommerzielles,
		&nbsp;soziales Netzwerk für mehr Nachhaltigkeit zur Verfügung zu
		stellen, um so in Zukunft noch mehr Lebensmittel vor dem Müll retten
		zu können!</span>
</p>
<p class="c1 c3">
	<span></span>
</p>
<p class="c1" style="text-align:center;">
	<span
		style="overflow: hidden; display: inline-block; margin: 0.00px 0.00px; border: 0.00px solid #000000; transform: rotate(0.00rad) translateZ(0px); -webkit-transform: rotate(0.00rad) translateZ(0px); width: 624.00px; height: 378.67px;"><img
		src="http://www.yunity.org/img/team.jpg"
		style="width: 624.00px; height: 378.67px; margin-left: 0.00px; margin-top: 0.00px; transform: rotate(0.00rad) translateZ(0px); -webkit-transform: rotate(0.00rad) translateZ(0px);"
		title=""></span><br />
		(Orgateam - Fusionsgespräche Berlin)
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1">
	<span class="c5">Bei den Fusionsgesprächen haben wir uns auf den
	</span><span class="c5"><a class="c6"
		href="http://wiki.lebensmittelretten.de/Fusionsgespr%C3%A4che_-_Konsens">Konsens</a></span><span
		class="c5">&nbsp;einigen können, dass wir uns in Bezug auf </span><span
		class="c4">Bürokratie und Geld auf \'so wenig wie möglich und so
		viel wie nötig\'</span><span class="c5">&nbsp;beschränken möchten.
		Dadurch ändert sich für Dich nichts – Du musst natürlich nach wie vor
		nichts bezahlen und auch keinem Verein beitreten. Des Weiteren werden
		wir unser Bestes geben, dass für den Verein in Zukunft keine Kosten
		mehr entstehen – das heißt, dass wir die bis dato einzige noch
		bezahlte Minijobstelle der Geschäftsführung des Vereins in Köln auch
		in eine ehrenamtliche Stelle umwandeln werden. Lebst Du in Köln und
		hast </span><span class="c5">Lust, Zeit und Freude diese Aufgaben zu
		übernehmen</span><span class="c5">? Informiere Dich </span><span
		class="c5 c12"><a class="c6"
		href="http://www.lebensmittelretten.de/?page=blog&sub=read&id=177">hier</a></span><span
		class="c5">.</span>
</p>
<p class="c1 c3">
	<span class="c5"><a class="c6"
		href="http://www.strommanufaktur.net/unser-engagement/online-voting.html"></a></span>
</p>
<p class="c1">
	<span class="c5">Falls Du noch detailliertere Infos zur Fusion
		möchtest, erfährst Du hier mehr über den </span><span class="c5 c12"><a
		class="c6"
		href="http://wiki.lebensmittelretten.de/Fusionsgespr%C3%A4che_-_Konsens">Konsens</a></span><span
		class="c5">&nbsp;der Fusionsgespräche.</span><span><br></span>
</p>
<p class="c1">
	<span class="c7">+++ 12.12.2014 / 12:12 Uhr /
		Foodsharing-Kundgebung auf der Domplatte +++</span>
</p>
<p class="c1 c3">
	<span></span>
</p>
<p class="c1">
	<span>Anlässlich des zweijährigen Bestehens findet am 12.12.2014
		um 12:12 Uhr eine öffentliche Kundgebung auf der Kölner Domplatte
		statt. Aus geretteten Lebensmitteln werden wir den Schriftzug
		\'foodsharing\' legen und Menschen formen gemeinsam die Worte \'Stop Food
		Waste\'. Im Anschluss werden die Lebensmittel kostenlos
		verteilt.&nbsp;Im Rahmen dieses Flashmobs möchten wir auf die enorme
		Verschwendung von Lebensmitteln aufmerksam machen: Mit dem
		Lebensmittelmüll, der alleine in Deutschland innerhalb von nur 7 Tagen
		anfällt, ließe sich der gesamte Kölner Dom füllen!</span>
</p>
<p class="c1 c3">
	<span></span>
</p>
<p class="c1" style="text-align:center;">
	<span
		style="overflow: hidden; display: inline-block; margin: 0.00px 0.00px; border: 0.00px solid #000000; transform: rotate(0.00rad) translateZ(0px); -webkit-transform: rotate(0.00rad) translateZ(0px); width: 528.00px; height: 285.00px;"><img
		src="http://www.yunity.org/img/essen.jpg"
		style="width: 528.00px; height: 285.00px; margin-left: 0.00px; margin-top: 0.00px; transform: rotate(0.00rad) translateZ(0px); -webkit-transform: rotate(0.00rad) translateZ(0px);"
		title=""></span>
</p>
<p class="c1 c3">
	<span></span>
</p>
<p class="c1">
	<span class="c5">Mehr Informationen gibt es hier </span><span
		class="c5 c12"><a class="c6"
		href="http://www.lebensmittelretten.de/?page=blog&sub=read&id=181">www.lebensmittelretten.de/?page=blog&amp;sub=read&amp;id=181</a></span>
</p>
<p class="c1">
	<span class="c5">bzw. hier der Link zur </span><span class="c5 c12"><a
		class="c6"
		href="https://www.facebook.com/events/403777079777314/">facebook
			Veranstaltung</a></span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1 c3">
	<span class="c4"></span>
</p>
<p class="c1">
	<span class="c4">2. foodsharing gewinnt den Engagement Wettwerb
	</span>
</p>
<p class="c1 c3">
	<span class="c4"></span>
</p>
<p class="c1">
	<span class="c5 c15">Dank 1975 Menschen die im Rahmen des
		Strom.Manufaktur-Votings 2014 für foodsharing abgestimmt haben,
		bekommt der foodsharing e.V. eine Spende in Höhe von 3.000 Euro. Wir
		bedanken uns bei allen die mitgestimmt und Werbung für die Abstimmung
		gemacht haben! Das Preisgeld wird ausschließlich für evtl. anfallende
	</span><span class="c2">Heizungskosten, Versicherungen und anderen
		Umkosten von foodsharing ausgegeben, die nicht durch Partner gedeckt
		werden können. Mehr Informationen:</span><span class="c5">&nbsp;</span><span
		class="c10 c5"><a class="c6"
		href="http://www.strommanufaktur.net/unser-engagement/online-voting.html">www.strommanufaktur.net/unser-engagement/online-voting.html</a></span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1 c3">
	<span class="c7"></span>
</p>
<p class="c1">
	<span class="c7">3</span><span class="c7">. Internationales
		Treffen</span>
</p>
<p class="c1 c3">
	<span></span>
</p>
<p class="c1">
	<span>Für das 4. internationale foodsharing-Treffen steht ein
		Termin fest! Es wird vom 30. April - 3. Mai 2015 im FEZ in
		Berlin-Köpenick stattfinden und wie immer vollkommen kostenfrei sein.
		Es wäre wunderbar, wenn auch Du dabei bist und Dir den Termin schon
		einmal vormerkst. Es wird das größte foodsharing-Treffen aller Zeiten
		werden und einen neuen Meilenstein der Bewegung markieren. Mehr
		Informationen zum Ort, an dem es für Groß und Klein viel zu entdecken
		gibt: </span><span class="c10"><a class="c6"
		href="http://www.fez-berlin.de/">www.fez-berlin.de</a></span><span>.
		Eine entsprechende Anmeldung wird es im Januar geben.</span>
</p>
<p class="c1">
	<span>Bis zum </span><span>15. Dezember</span><span>&nbsp;2014</span><span>,
		12 Uhr,</span><span>&nbsp;möchte das Event-Orgateam gerne noch in
		Erfahrung bringen, welche Workshops Euch interessieren oder gar welche
		ihr noch anbieten möchtet. Den Link zur Umfrage findet ihr </span><span
		class="c10"><a class="c6"
		href="http://umfrage.lebensmittelretten.de/index.php/162641/lang-de-informal">hier</a></span><span>.</span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1">
	<span class="c4">4</span><span class="c4">. ÜbersetzerInnen
		&amp; VersicherungsrechtlerIn gesucht</span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1">
	<span class="c5">Im Moment gibt es die foodsharing-Bewegung
		nicht nur in Deutschland, sondern auch in Österreich und der Schweiz.
		Für Menschen aus nicht-deutschsprachigen Ländern ist es allerdings
		gerade sehr schwierig, aktiv zu werden, weil es die Webseite noch nicht
		in anderen Sprachen gibt. Wenn Du eine Fremdsprache auf
		Muttersprachenniveau beherrschst, dann kannst Du es
		foodsharing-Bewegungen in anderen Ländern ermöglichen, ebenfalls
		unsere Plattform zu benutzen, indem Du uns bei der Übersetzung hilfst!
		Meld Dich dafür in den dazugehörigen Gruppen bzw. melde Dich unter: </span><span
		class="c10 c5"><a class="c6"
		href="mailto:international@lebensmittelretten.de">international@lebensmittelretten.de</a></span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1">
	<span class="c5">Wir sind auf der Suche nach einer Person, die
		sich im </span><span class="c4">Versicherungsrecht</span><span class="c5">&nbsp;gut
		auskennt und zukünftig foodsharing, wie alle anderen PartnerInnen,
		kostenfrei beratend unterstützt. Falls Du davon Ahnung hast, oder
		jemanden kennst, dann würden wir uns bei </span><span class="c10 c5"><a
		class="c6" href="mailto:orgateam@lebensmittelretten.de">orgateam@lebensmittelretten.de</a></span><span
		class="c5">&nbsp;sehr über eine E-Mail freuen. :-)</span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1">
	<span class="c4">5. foodsharing walk</span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1">
	<span class="c5">Um noch bedingungsloser und vor allem mit
		voller Energie für foodsharing zu wirken, wird sich unser
		Programmierer Raphael Wintrich, der bereits über 2.000 Stunden
		ehrenamtlich programmiert hat, auf eine </span><span class="c4">Wander-Wirkungs-Reise</span><span
		class="c5">&nbsp;begeben.</span>
</p>
<p class="c1">
	<span class="c5">Auf dieser Reise wird er vielen Menschen aus
		unserer wunderbaren foodsharing-Familie begegnen. Auf seinem Blog </span><span
		class="c5"><a class="c6"
		href="http://geldfrei.net/">geldfrei.net</a></span><span
		class="c5">&nbsp;kannst Du verfolgen, wo er sich gerade
		befindet. Sehr gerne freut er sich auch über Reisebegleitung, falls
		auch Du für einen oder mehrere Tage Deinen Alltag verlassen willst.</span>
</p>
<p class="c1">
	<span class="c5">Außerdem können wir ihm und dem ganzen Projekt
		eine große Hilfe sein, indem wir ihm für ein paar Tage einen </span><span
		class="c4">Ort zum Schlafen und Wirken anbieten</span><span class="c5">,
		wo er in Ruhe programmieren kann.</span>
</p>
<p class="c1">
	<span class="c5">Er benötigt lediglich einen Internetzugang und
		einen Platz, auf dem er seinen Schlafsack ausrollen kann. Für alle
		IT-Interessierten wird es ca. 1x im Monat an unterschiedlichen Orten
		Hacker-Spaces geben. Zwei gab es bereits. Den ersten gabs vom
		31.10.-2.11. in Mainz und den 2. vom 15.-16 November in Paris, es
		wurde viel gewerkelt und gab superleckeres Essen mit
		foodsharing-Lebensmittel. Während des Zusammenkommens der IT-Genies
		von foodsharing wird dann für mehrere Tage gemeinsam in angenehmer
		foodsharing-Atmosphäre programmiert. In dem Rahmen soll aber nicht nur
		die Plattform verbessert und die Internationalisierung vorangetrieben
		werden, sondern die IT-Interessierten Menschen sollen sich
		untereinander kennen lernen und über IT, Entwicklung und Design
		austauschen können. Wenn Du wissen willst, wo Events stattfinden, oder
		Du Raphael zu Dir nach Hause einladen möchtest, besuche seinen
		Reise-Blog unter </span><span class="c5"><a class="c6"
		href="http://geldfrei.net/">geldfrei.net.</a></span>
</p>
<p class="c1 c3">
	<span></span>
</p>
<p class="c1 c3">
	<span class="c5"><a class="c6"
		href="http://geldfrei.net/"></a></span>
</p>
<p class="c1">
	<span class="c4">6</span><span class="c4">. Für den “Genießt
		uns! Award” werden noch Lebensmittelbetriebe, die sich gegen
		Verschwendung einsetzen, gesucht:</span>
</p>
<p class="c1 c3">
	<span class="c4"></span>
</p>
<p class="c1">
	<span class="c5">Der Unternehmens-Wettbewerb von </span><span
		class="c5"><a class="c6"
		href="https://www.facebook.com/geniesstuns">Genießt
			uns</a></span><span class="c5">, der gemeinsamen Initiative von
		foodsharing, Welthungerhilfe, WWF Deutschland, Tafel,
		Verbraucherzentrale NRW und "United Against Waste" sucht noch bis zum
		15. Dezember nach vorbildlichen Unternehmen, die Essen wertschätzen
		und etwas gegen die Verschwendung von Lebensmitteln unternehmen. Leite
		den Aufruf zum Mitmachen an die Verantwortlichen weiter und frag sie,
		ob ob sie nicht teilnehmen wollen! Mehr Informationen gibt es hier: </span><span
		class="c5 c10"><a class="c6"
		href="http://www.geniesstuns.de/unternehmenscheck/mitmachen">www.geniesstuns.de/unternehmenscheck/mitmachen</a></span><span
		class="c5">.</span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1">
	<span class="c4">7</span><span class="c4">. Wegen der Fusion
		sind die foodsharing Webseiten (D, AT, CH) ab heute Abend (Donnerstag)
		nicht mehr erreichbar und der Countdown zur fusionierten-Seite beginnt.</span><span class="c4">&nbsp; </span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1">
	<span class="c5">Endlich ist es soweit. Ab Freitag Mittag um
		12.12 Uhr wird es nur noch eine Webseite geben die
		lebensmittelretten.de &amp; und die foodsharing Webseiten vereint, </span><span
		class="c5">dabei werden die vorhanden Domains für Deutschland,
		Österreich und die Schweiz bestehen bleiben.</span>
</p>
<p class="c1">
	<span class="c5">Die Fusionsarbeiten beginnen Heute um 23 Uhr.
		Die foodsharing Webseiten werden dann ab Freitag Mittag um 12.12 Uhr
		mit all seinen neuen Funktionen wieder zu erreichen sein.</span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1">
	<span class="c5">Ein Tipp zur Weihnachtszeit:</span><span class="c4">&nbsp;Die
		geniale unkommerzielle Initiative</span><span class="c5">&nbsp;</span><span
		class="c10 c5"><a class="c6"
		href="http://www.zeit-statt-zeug.de/">Zeit
			statt Zeug</a></span><span class="c5">, bietet viele wunderschöne
		Anregungen für kostenfreie, nachhaltige und kreative Geschenk an.</span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1">
	<span class="c5">Euch allen gemütliche </span><span class="c5">Advents</span><span
		class="c5">tage mit viel </span><span class="c4">Sonne und
		wärme im Herzen</span><span class="c5">&nbsp;wünscht Euch das gesamte
		foodsharing-Team</span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1 c3">
	<span class="c5"></span>
</p>
<p class="c1"><br /></p>
<p class="c1"><br /></p>
<p class="c1" style="text-align:center;">
	<span class="c5"><a name="english">_________________ ENGLISH NEWSLETTER VERSION ________________</a></span>
</p>
<p class="c1">
	<span class="c7">2 years of foodsharing: Tens of thousands of
		foodsharers have already saved over a million kilos (about 2.2 million
		lbs) of food &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span>&nbsp;
		&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
		&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span>
</p>
<p class="c1 c3">
	<span></span>
</p>
<p class="c1">
	<span>On the 12th of December 2014 the </span><span>\'foodsharing\'
	</span><span>movement will celebrate its second anniversary, together
		with its 60,000 members, who have been working together to raise
		awareness and appreciation for food. </span>
</p>
<p class="c1 c3">
	<span></span>
</p>
<p class="c1">
	<span>The online platform www.foodsharing.de has made it
		possible for individuals in Germany, Austria and Switzerland to share
		their surplus food with people in their neighbourhood, completely free
		of charge. And in addition to that, over 9,000 voluntary “foodsavers”
		have already saved and distributed about 3 million euros worth of free
		food through collaborations with over a thousand supermarkets,
		bakeries and other enterprises. A large amount of the rescued food </span><span>is
		made available</span><span>&nbsp;to people at “fair share points” -
		shelves or refrigerators that are set up in public places - where
		everyone is free to come and help themselves, and there is no charge.</span>
</p>
<p class="c1 c3">
	<span></span>
</p>
<p class="c1">
	<span>After having received more than 20 million page views, the
		f</span><span>oodsharing </span><span>website will be launching a
		completely new design and be merged together with the volunteer
		platform </span><span class="c10 c18"><a class="c6"
		href="http://www.lebensmittelretten.de/">www.lebensmittelretten.de</a></span><span>&nbsp;on
		the </span><span>12th of December 2014</span><span>.</span>
</p>
<p class="c1 c3">
	<span></span>
</p>
<p class="c1">
	<span>A special feature of the </span><span>foodsharing</span><span>&nbsp;movement
		is that it is entirely volunteer-based. From carrying out the
		programming and web design, to handling all the public relations and
		organisational tasks, the f</span><span>oodsharing </span><span>volunteers
		work in a wide variety of areas. And on top of all that, free
		assistance is also offered by various service providers - print shops,
		legal advisers and internet service providers, to name just a few -
		&nbsp;all of whom make it possible for f</span><span>oodsharing </span><span>to
		remain free of charge and free from advertising in the future too.</span>
</p>
<p class="c1">
	<span>&nbsp;</span>
</p>
<p class="c1">
	<span>Under the motto: </span><span>\'think globally, act
		locally\', </span><span>f</span><span>oodsharing </span><span>has, in a
		very short time, become one of the fastest growing social movements in
		the German speaking world. The next goal is to further develop the
		website, in order to be able to provide the sharing culture with a
		non-commercial social network, where sustainability can promoted and
		in the future even more food can be saved from becoming waste!</span>
</p>
<p class="c1">
	<span>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
		&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
		&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
		&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span>
</p>
<p class="c1">
	<span class="c7">+++ 12.12.2014 / 12:12 pm / Foodsharing Rally
		at Cologne Cathedral Square +++</span>
</p>
<p class="c1">
	<span>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
		&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
		&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
		&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span>
</p>
<p class="c1">
	<span>In honour of f</span><span>oodsharing’s </span><span>second
		anniversary, a public rally will be held at Cologne Cathedral Square
		(the so-called “Domplatte”) on 12/12/2014 at 12:12 pm. Demonstrators
		will lay out rescued food to form the words </span><span>\'foodsharing\'
	</span><span>and all the demonstrators will join together to spell out
		the words \'Stop Food Waste\'. Following the rally, the food will be
		handed out free of charge. This flash mob has been organized by the
		members of the f</span><span>oodsharing movement </span><span>in order
		to bring attention to the issue of food waste. The amount of food that
		Germany throws away within just seven days would be enough to fill the
		whole of Cologne Cathedral!</span>
</p>
</div>
<footer>
-- 
<p>Du möchtest keinen Newsletter mehr erhalten? Kein Problem! <a href="http://www.lebensmittelretten.de/?page=login&sub=unsubscribe&e=' . urlencode($email) .'">Einfach hier klicken um Dich vom Newsletter abzumelden.</a></p>	
<p style="font-size:11px;">
<strong>Impressum</strong><br />
Angaben gemäß § 5 TMG:<br />
<br />
Foodsharing e.V.<br />
Marsiliusstr 36<br />
50937 Köln<br />
Vertreten durch:<br />
<br />
Raphael Fellmer, Raphael Wintrich und Valentin Thurn<br />
Kontakt:<br />

E-Mail: info@lebensmittelretten.de<br />
Registereintrag:<br />
<br />
Eintragung im Vereinsregister.<br />
Registergericht: Amtsgericht Köln<br />
Registernummer: VR 17439<br />
Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV:<br />
<br />
Raphael Fellmer, Raphael Wintrich und Valentin Thurn<br />
</p>
</footer>
</body>
</html>';
	}
	
	private function sendNewsletter($address,$email)
	{
		$email->clearRecipients();
		$email->addRecipient($address);
		
		/*
		$email->addRecipient('raphi@waldorfweb.net');
		$email->send($this->smtp);
		
		$email->clearRecipients();
		$email->addRecipient('kingofdust@gmail.com');
		$email->send($this->smtp);
		
		$email->clearRecipients();
		$email->addRecipient('r.fellmer@lebensmittelretten.de');
		$email->send($this->smtp);
		die();
		*/
		$max_try = 2;
		while ($max_try > 0)
		{
			try {
				//info('send');
				$email->send($this->smtp);
				//success('OK');
				
				$max_try = 0;
				
				return true;
			} catch (Exception $e) {
				error('failed...retry');
				$max_try--;
				sleep(5);
				$this->smtpReconnect();
			}
		}
		
		
		return false;
	}
	
	private function smtpReconnect()
	{
		if($this->smtp !== false)
		{
			@$this->smtp->close();
			sleep(5);
		}
		try {
			$this->smtp= new fSMTP('mail.manitu.de',465,true);
			$this->smtp->authenticate('raphael@tasteofheimat.org', 'h4nn3lor3');
		} catch (Exception $e) {
			error('cant connect to smtpwait10 sec.');
			sleep(10);
			$this->smtp= new fSMTP('mail.manitu.de',465,true);
			$this->smtp->authenticate('raphael@tasteofheimat.org', 'h4nn3lor3');
		}
		
		
		/*
		$this->smtp= new fSMTP('smtp.gmail.com',465,true);
		$this->smtp->authenticate('koeln.foodsharing@gmail.com', 'EssenRetten2013');
		*/
		/*
		$this->smtp = new fSMTP('kunden.greensta.de',25);
		$this->smtp->authenticate('admin@lebensmittelretten.de', 'Sj6PKGB7F/EUa,');
		*/
	}
	
	private function filterSended($users,$sendet)
	{
		$t_send = array();
		foreach ($sendet as $s)
		{
			$t_send[$s['email']] = true;
		}
		
		$tmp = array();
		
		foreach ($users as $s)
		{
			if(!isset($t_send[$s['email']]))
			{
				$tmp[] = $s;
			}
		}
		
		return $tmp;
	}
	
	public function chats()
	{
		info('getold conversations');
		
		$count_complete = (int)$this->model->qOne('SELECT COUNT(id) FROM fs_message WHERE sender_id != 0 AND recip_id != 0');
		
		if($convs = $this->model->listOldConversations())
		{
			file_put_contents('convs.txt',print_r($convs,true));
			success(count($convs).' conversations found');
			$bar = $this->progressbar($count_complete);
			$x=0;
			$cur_msg_count = 0;
			foreach ($convs as $c)
			{
				$bar->update($cur_msg_count);
				$x++;
				
				$recip1 = array_shift($c);
				$recip2 = end($c);				

				if($conversation_id = $this->model->getConversationId($recip1,$recip2))
				{
					
					$mindate = '';
					$maxdate = '';
					$unread = 0;
					$last_foodsaver_id = 0;
					$last_message = '';
					$last_message_id = 0;
					
					if($messages = $this->model->listOldMessages($recip1,$recip2))
					{
						$i = 0;
						foreach ($messages as $msg)
						{
							$cur_msg_count++;
							$i++;
							if($i == 1)
							{
								$mindate = $msg['time'];
								//info($mindate);
							}
							
							$body = str_replace(array('<br />','<br>','<br/>','<p>','</p>'),"\n",$msg['msg']);
							$body = strip_tags($body);
							$body = trim($body);
							$id = $this->model->addMsg($conversation_id,$msg['sender_id'],$body,$msg['time']);
						
							if($i == count($messages))
							{
								$maxdate = $msg['time'];
								$unread = $msg['unread'];
								$last_foodsaver_id = $msg['sender_id'];
							
								$body = str_replace(array('<br />','<br>','<br/>','<p>','</p>'),"\n",$msg['msg']);
								$body = strip_tags($body);
								$body = trim($body);
							
								$last_message = $body;
								$last_message_id = $id;
								//info('max: '.$maxdate);
							}
							
						}
					}
					
					$this->model->connectUser($conversation_id,$recip1,$recip2,$unread);
					
					$this->model->updateConversation(
						$conversation_id, 
						$maxdate, 
						$mindate, 
						$last_foodsaver_id, 
						$last_message, 
						$last_message_id
					);
				}
			}
		}
		else
		{
			error('no conversations found');
		}
	}
}
