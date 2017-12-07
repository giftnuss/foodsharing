<?php

namespace Foodsharing\Modules\Index;

use Foodsharing\Lib\View\vPageslider;
use Foodsharing\Modules\Core\View;

class IndexView extends View
{
	public function index($first_content, $gerettet)
	{
		$ps = new vPageslider();

		$ps->addSection($this->campaign($first_content), array(
			'color' => '#48bac4', //pc_foodporn.png
			'color' => '#eb7763', //pc_ei.png
			'color' => '#7b4a94', //pc_bread.png
			'color' => '#fbbd13', //pc_sauer.png
			'color' => '#26ad91', //pc_dickmilch.png
			'anchor' => 'kampagne'
		));

		// After the campaign is finished these lines will replace the campaign section again
//		$ps->addSection($first_content, array(
//			'color' => '#4a3520',
//			'anchor' => 'oben'
//		));

		$ps->addSection($this->welcome(), array(
			'anchor' => 'wilkommen'
		));

		$ps->addSection($this->howto(), array(
			'color' => '#ffffff',
			'anchor' => 'video'
		));

		$ps->addSection($this->joinus(), array(
			'color' => '#ffffff',
			'anchor' => 'mach-mit',
		));

		$ps->addSection($this->whatiswhat(), array(
			'anchor' => 'was-ist-was'
		));

		$ps->addSection($this->withfooter(), array(
			'anchor' => 'unten',
		));

		return $ps->render();
	}

	private function campaign($first_content)
	{
		return '
		<div class="pure-g carpetroll">
			<div class="pure-u-1 pure-u-sm-1-2" id="campaign">
				<img src="/img/pc_text.png" id="campaigntext" alt="Don’t let good food go bad!">
			</div>
			<div class="pure-u-1 pure-u-sm-1-2">
				<img src="/img/foodx.png" id="campaignimg">
			</div>
		</div>
		<div style="font-size:5px;position:absolute;bottom:0;right:0;color:#ffffff;">Pink Carrots</div>
		';
	}

	private function welcome()
	{
		return '
		<div class="pure-g carpetroll">
			<div class="pure-u-1 pure-u-md-1-2">
				<h3>Willkommen bei foodsharing!</h3>
			</div>
			<div class="pure-u-1 pure-u-md-1-2">
				<p>Wir sind eine Initiative, die sich gegen Lebensmittelverschwendung engagiert. Wir “retten” ungewollte und überproduzierte Lebensmittel in privaten Haushalten sowie von kleinen und großen Betrieben. </p>
				<p>Darüber hinaus verstehen wir uns als bildungspolitische Bewegung und fühlen uns nachhaltigen Umwelt- und Konsumzielen verpflichtet. Wir setzen uns unter anderem für einen Wegwerf-Stopp und gegen den Verpackungs-Wahnsinn der Supermärkte ein. Mit diesen und anderen Themen sind wir auf Veranstaltungen oder in Medien präsent und starten eigene Aktionen.  </p>
				<p>Die Organisation unserer foodsharing-Community und unserer Aktivitäten läuft in erster Linie über die Online-Plattform foodsharing.de. Hier vernetzen und koordinieren sich die Lebensmittelretter*innen (Foodsharer/Foodsaver) in den einzelnen Städten und Regionen. Über die Plattform werden überregionale Themen, Veranstaltungen und Informationen veröffentlicht.</p>
				<p>Unsere foodsharing-Initiative entstand 2012 in Berlin. Mittlerweile ist sie zu einer internationalen Bewegung herangewachsen mit über 200.000 registrierten Nutzern*innen in Deutschland, Österreich, der Schweiz und weiteren europäischen Städten. </p>
				<p>Die Mitglieder der foodsharing-Community arbeiten ehrenamtlich und unentgeltlich. foodsharing.de ist und bleibt kostenlos, nicht kommerziell, unabhängig und werbefrei. Wir wollen die Plattform open source und weltweit leichter zugänglich machen – so wie es das foodsharing-Konzept des Lebensmittelretten bereits ist.</p>
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
			<div class="pure-g carpetroll">
				<div class="pure-u-1 pure-u-md-1-2">
					<h3>Wie funktioniert foodsharing?</h3>
				</div>
				<div class="pure-u-1 pure-u-md-1-2">
					<p>Was steckt hinter unserer Initiative und wie kannst Du Dich einbringen?
					Diese und weitere Fragen beantworten wir mit diesem Video.</p>
					<p><a class="vidlink" href="https://www.youtube-nocookie.com/embed/dqsVjuK3rTc?rel=0"><img class="corner-all" src="/img/howto.jpg" /></a></p>
				</div>
			</div>
		</div>';
	}

	private function joinus()
	{
		return '
		<div class="pure-g carpetroll">
			<div class="pure-u-1 pure-u-md-1-2">
				<h3>Werde aktiv und rette mit!</h3>
			</div>
			<div class="pure-u-1 pure-u-md-1-2">
				<p>Wir laden Dich ein, bei foodsharing aktiv zu werden und Dich gemeinsam mit tausenden Gleichgesinnten gegen Lebensmittelverschwendung zu engagieren. </p>
				<p>Registriere Dich bei foodsharing.de und werde Teil unserer Community. Du kannst hier unsere Aktivitäten und Mitglieder in Deinem Bezirk kennenlernen und Dich gemeinsam mit ihnen für eine gute Sache einsetzen.</p>
				<p><a href="/?page=content&sub=joininfo" class="button" >Mitmachen</a></p>
			</div>
		</div>
		';
	}

	private function whatiswhat()
	{
		return '
		<div class="pure-g carpetroll">
			<div class="pure-u-1">
				<h3>Was ist das?</h3>
			</div>
			<div class="pure-u-1 pure-u-md-1-5">
				<h2><span class="clickable" onclick="alert(\'Sobald Du Dich bei foodsharing.de registriert hast, bist Du als Lebensmittelretter*in (Foodsharer*in) der Community beigetreten. Als Foodsharer*in kannst Du Essenskörbe anbieten oder abholen, an foodsharing-Events teilnehmen und beim Fair-Teiler Lebensmittel hinbringen oder mitnehmen. \n\nWenn Du als Foodsharer*in von Betrieben Lebensmittel retten möchtest, dann sind zwei weitere Schritte notwendig. Erster Schritt ist Deine Quiz-Teilnahme mit foodsharing-spezifischen Fragen. Zweiter Schritt sind drei betreute Einführungs-Abholungen bei einem Betrieb. \n\nAls Foodsaver*in bist Du eine freiwillig, aktiv-agierende Person, die es sich zum Ziel macht, Lebensmittel vor dem Wurf in die Tonne zu bewahren. Du wirst Lebensmittel in Absprache mit anderen Foodsaver*innen und kooperierenden Betrieben einsammeln und fair-teilen. Des weiteren ist es sinnvoll und wünschenswert, wenn Du mit der Community in Kontakt bist und an Events und Bezirkstreffen teilnimmst. \n\nFalls Du Interesse an weitreichenderen Aufgaben hast, gibt es verantwortungsvolle Tätigkeiten beispielsweise als Mitglied einer Arbeitsgruppe, als Betriebsverantwortliche*r oder Botschafter*in.\
					\');"><i class="fa fa-smile-o whatiswhaticons" aria-hidden="true"></i>
				<span class="iconlegend">Foodsharer und Foodsaver</span></span></h2>
			</div>
			<div class="pure-u-1 pure-u-md-1-5">
				<h2><span class="clickable" onclick="alert(\'Du kannst Deine privaten Lebensmittel, die Du nicht verwendest, kostenlos der lokalen foodsharing Community anbieten oder angebotene Lebensmittel von anderen Mitgliedern aus der Community abholen. Über die Plattform könnt ihr Kontakt miteinander aufnehmen, um die Übergabe der angebotenen Lebensmittel zu vereinbaren.\
					\');"><i class="fa fa-shopping-basket whatiswhaticons" aria-hidden="true"></i>
				<span class="iconlegend">Essenskörbe</span></span></h2>
			</div>
			<div class="pure-u-1 pure-u-md-1-5">
				<h2><span class="clickable" onclick="alert(\'Ein Fair-Teiler besteht beispielsweise aus einem Regal und/oder einem Kühlschrank. Fair-Teiler werden meist an gut zugänglichen Orten aufgestellt, so dass jede*r Lebensmittel bringen und/oder kostenlos mitnehmen darf.\n\nFair-Teiler können auf unserer Plattform eingetragen werden. Die Veröffentlichung hat den Vorteil, dass der Fair-Teiler für alle registrierte und unregistrierte Nutzer*innen sichtbar wird.\n\nEin Fair-Teiler kann in privaten Räumen oder in Räumen der Stadt, der Uni, eines Vereins usw. untergebracht sein. Unsere Fair-Teiler-Regeln und Voraussetzungen, wie beispielsweise der Hygieneplan und die Verantwortlichkeit eines/einer betriebsverantwortlichen Foodsaver*in sind in jedem Fall zu beachten. Es gibt Fair-Teiler, die geregelte Öffnungszeiten haben, und solche, die rund um die Uhr zugänglich sind.\
					\');"><i class="fa fa-refresh whatiswhaticons" aria-hidden="true"></i>
				<span class="iconlegend">Fair&#8209;Teiler</span></span></h2>
			</div>
			<div class="pure-u-1 pure-u-md-1-5">
				<h2><span class="clickable" onclick="alert(\'Unsere kooperierenden Betriebe sind Bäckereien, Obst-und Gemüsehandel, Filialen von Supermärkten, Wochenmärkte, Restaurants, Kantinen, Cafés, Catering-Services, Großhandel, Bauernhöfe und alle weiteren Arten lebensmittelverarbeitender Betriebe.\n\nDas Tagesgeschäft versuchen wir, so wenig wie möglich zu beeinträchtigen. Die Abholungen bei kooperierenden Betrieben werden im Vorfeld abgestimmt und vereinbart. Dadurch möchten wir zusätzlichen Aufwand für die Betriebsmitarbeiter*innen vermeiden.\n\nFür jeden Betrieb gibt es seitens foodsharing eine*n betriebsverantwortliche*n Foodsaver*in. Diese*r wahrt die Bedürfnisse des Betriebs und des zuständigen foodsharing Teams. Des weiteren kümmert er/sie sich um die Koordination zuverlässiger Abholungen.\n\nFür kooperierende Betriebe fallen durch unsere Abholungen weniger kostenpflichtige Lebensmittelabfälle an. Durch die Kooperation mit uns kann ein Betrieb sich bewusst dafür entscheiden, ein Zeichen gegen die Lebensmittelverschwendung zu setzen.\
					\');"><i class="fa fa-home whatiswhaticons" aria-hidden="true"></i>
				<span class="iconlegend">Betriebe</span></span></h2>
			</div>
			<div class="pure-u-1 pure-u-md-1-5">
				<h2><span class="clickable" onclick="alert(\'foodsharing ist jedes Jahr auf vielen Veranstaltungen und Festivals vertreten. Dort möchten wir auf unsere Initiative und Engagement aufmerksam machen. Darüber hinaus geben wir ein klares Statement gegen Lebensmittel- und Ressourcenverschwendung ab und plädieren für Nachhaltigkeit in Umwelt- und Konsumfragen. Wir nutzen dafür Workshops oder Vorträge, Filmvorführungen und anschließende Diskussionen oder eigene Infostände mit und ohne Essensverteilung.\n\nEine unserer Bedingungen ist direkt mit Festival-Betreiber*innen zu kooperieren und auf dem Veranstaltungsgelände aktiv zu werden. Bitte nehmt dafür gerne mit uns Kontakt auf. Wir retten auf Festivals überschüssige Lebensmitteln von Besucher*innen oder Foodständen. Des Weiteren sammeln wir auch zurückgelassene Dinge, die noch brauchbar sind - vor allem am Abreisetag eines Festivals.\
					\');"><i class="fa fa-music whatiswhaticons" aria-hidden="true"></i>
				<span class="iconlegend">Festivals</span></span></h2>
			</div>
		</div>
		';
	}

	private function withfooter()
	{
		return '
			<a href="/?page=content&sub=joininfo" id="joinbuttoninfootersection">Rette mit!</a>
		';
	}
}
