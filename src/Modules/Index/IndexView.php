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
			'color' => '#48bac4', //foodporn
			'color' => '#eb7763', //faulesei.png
			'color' => '#7b4a94', //walkingbread.png
			'color' => '#fbbd13', //sauregurken.png
			'color' => '#26ad91', //dickmilch.png
			'anchor' => 'kampagne'
		));

//		// After the campaign is finished these lines will replace the campaign section again
//		$ps->addSection($first_content, array(
//			'color' => '#4a3520',
//			'anchor' => 'oben'
//		));

		$ps->addSection($this->welcome(), array(
			'color' => '#F9F5E0',
			'anchor' => 'wilkommen'
		));

		$ps->addSection($this->howto(), array(
			'anchor' => 'video'
		));

		$ps->addSection($this->joinus(), array(
			'color' => '#F9F5E0',
			'anchor' => 'mach-mit',
		));

		$ps->addSection($this->whatiswhat(), array(
			'anchor' => 'was-ist-was'
		));

		$ps->addSection($this->withfooter(), array(
			'color' => '#F9F5E0',
			'anchor' => 'unten',
		));

		return $ps->render();
	}

	private function campaign($first_content)
	{
		return '
		<div id="campaign" class="pure-g dickmilchbeta ">
			<div class="topbarpadding">
				<div id="campaigntext" class="pure-u-1 pure-u-sm-1-2">
					<h2>Don’t&nbsp;let good&nbsp;food&nbsp;go&nbsp;bad!</h2>
					<div><img src="/img/gabel.png"></div>
					<h4>Rette mit!</h4>
					<h3>food<span>sharing</span></h3>
				</div>
				<div id="campaignimg" class="pure-u-1 pure-u-sm-1-2">
				</div>
			</div>
		</div>
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
				<p>Darüber hinaus verstehen wir uns als bildungspolitische Bewegung und fühlen uns nachhaltigen Umwelt- und Konsumzielen verpflichtet. Wir setzen uns unter anderem für einen Wegwerfstopp und gegen den Verpackungswahnsinn der Supermärkte ein. Mit diesen und anderen Themen sind wir auf Veranstaltungen oder in Medien präsent und starten eigene Aktionen.</p>
				<p>Die Organisation unserer foodsharing-Community und unserer Aktivitäten läuft in erster Linie über die Online-Plattform foodsharing. Hier vernetzen und koordinieren sich die Lebensmittelretter*innen (Foodsharer/Foodsaver) in den einzelnen Städten und Regionen. Über die Plattform werden überregionale Themen, Veranstaltungen und Informationen veröffentlicht.</p>
				<p>Unsere foodsharing-Initiative entstand 2012 in Berlin. Mittlerweile ist sie zu einer internationalen Bewegung mit über 200.000 registrierten Nutzern*innen in Deutschland, Österreich, der Schweiz und weiteren europäischen Ländern herangewachsen.</p>
				<p>Die Mitglieder der foodsharing-Community arbeiten ehrenamtlich und unentgeltlich. Die Initiative foodsharing ist und bleibt kostenlos, nicht kommerziell, unabhängig und werbefrei. Wir wollen die Plattform open source und weltweit leichter zugänglich machen – so wie es das foodsharing-Konzept des Lebensmittelrettens bereits ist.</p>
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
				<p>Wir laden Dich ein, bei foodsharing aktiv zu werden und Dich gemeinsam mit tausenden Gleichgesinnten gegen Lebensmittelverschwendung zu engagieren.</p>
				<p>Registriere Dich bei foodsharing und werde Teil unserer Community. Du kannst hier unsere Aktivitäten und Mitglieder in Deinem Bezirk kennenlernen und Dich gemeinsam mit ihnen für eine gute Sache einsetzen.</p>
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
				<h2><span class="clickable" onclick="alert(\'Sobald Du Dich bei foodsharing registriert hast, gehörst Du als Lebensmittelretter*in (Foodsharer) zur Community.\n\nAls Foodsharer kannst Du Essenskörbe anbieten oder abholen, an foodsharing-Events teilnehmen und Lebensmittel zum Fair-Teiler bringen oder von dort mitnehmen. Wenn Du als Foodsharer von Betrieben Lebensmittel retten möchtest, musst Du zwei weitere Schritte tun:\n1. Du nimmst an einem foodsharing-Quiz teil.\n2. Du absolvierst drei betreute Einführungsabholungen bei einem Betrieb.\n\nAls Foodsaver handelst Du aktiv und freiwillig mit dem Ziel, gute Lebensmittel nicht in der Tonne enden zu lassen. Du wirst in Absprache mit anderen Foodsavern Lebensmittel von den kooperierenden Betrieben einsammeln und fair-teilen. Es ist natürlich sinnvoll und wünschenswert, wenn Du mit der Community in Kontakt stehst und an Events und Bezirkstreffen teilnimmst.\n\nFalls Du Interesse an einem weiterreichenden Engagement hast, findest Du bei uns auch verantwortungsvolle Tätigkeiten, etwa als Mitglied einer Arbeitsgruppe, als Betriebsverantwortliche*r oder Botschafter*in.\
					\');"><i class="fa fa-smile-o whatiswhaticons" aria-hidden="true"></i>
				<span class="iconlegend">Foodsharer und Foodsaver</span></span></h2>
			</div>
			<div class="pure-u-1 pure-u-md-1-5">
				<h2><span class="clickable" onclick="alert(\'Du kannst Deine eigenen überschüssigen Lebensmittel der lokalen foodsharing-Community anbieten und Lebensmittel bei anderen Community-Mitgliedern abholen. Über die Plattform könnt Ihr Kontakt aufnehmen, um die Übergabe zu organisieren.\
					\');"><i class="fa fa-shopping-basket whatiswhaticons" aria-hidden="true"></i>
				<span class="iconlegend">Essenskörbe</span></span></h2>
			</div>
			<div class="pure-u-1 pure-u-md-1-5">
				<h2><span class="clickable" onclick="alert(\'Der Fair-Teiler ist wie ein „Umschlagplatz“ für Lebensmittel, zu dem Du Lebensmittel bringen und von dort gratis mitnehmen darfst. Als Fair-Teiler dient ein Regal und/oder ein Kühlschrank. Du findest unsere Fair-Teiler an gut zugänglichen Orten wie in privaten oder kommunalen Räumen, auf dem Uni-Gelände, in Vereinsräumen u. v. m. Die Fair-Teiler können auf unserer Plattform eingetragen und somit für alle Nutzer*innen auf einer Karte angezeigt werden.\n\nEs gibt Fair-Teiler mit geregelten Öffnungszeiten und solche, die rund um die Uhr zugänglich sind.\nUnsere Fair-Teiler-Regeln und -Voraussetzungen, etwa der Hygieneplan und die Verantwortlichkeit eines betriebsverantwortlichen Foodsavers sind in jedem Fall zu beachten.\
					\');"><i class="fa fa-refresh whatiswhaticons" aria-hidden="true"></i>
				<span class="iconlegend">Fair&#8209;Teiler</span></span></h2>
			</div>
			<div class="pure-u-1 pure-u-md-1-5">
				<h2><span class="clickable" onclick="alert(\'Unsere kooperierenden Betriebe sind Bäckereien, Obst- und Gemüsehändler, Filialen von Supermärkten, Wochenmärkte, Restaurants, Kantinen, Cafés, Catering-Services, der Großhandel, Bauernhöfe u. v. m.\n\nFür jede Kooperation mit einem Betrieb gibt es einen betriebsverantwortlichen Foodsaver. Außerdem kümmert er/sie sich um die Organisation zuverlässiger Abholungen. Wir sind bestrebt, das Tagesgeschäft der Betriebe so wenig wie möglich zu beeinträchtigen. Die Abholungen bei Betrieben werden daher im Vorfeld abgestimmt und vereinbart.\n\nDie foodsharing-Kooperation ist nachhaltig und eine Win-Win-Situation. Die Betriebe können dabei ihre Lebensmittelabfälle und damit verbundene Entsorgungskosten reduzieren und bewusst ein Zeichen gegen Lebensmittelverschwendung setzen.\
					\');"><i class="fa fa-home whatiswhaticons" aria-hidden="true"></i>
				<span class="iconlegend">Betriebe</span></span></h2>
			</div>
			<div class="pure-u-1 pure-u-md-1-5">
				<h2><span class="clickable" onclick="alert(\'foodsharing ist regelmäßig auf vielen Veranstaltungen und Festivals präsent, um auch dort auf die Initiativen und unser Engagement aufmerksam zu machen. Wir nutzen auch diese Gelegenheiten, um ein klares Statement gegen Lebensmittel- und Ressourcenverschwendung und für Nachhaltigkeit in Umwelt- und Konsumfragen abzugeben. Dafür veranstalten wir auf dem Festivalgelände Workshops oder Vorträge, Filmvorführungen und Diskussionsrunden und sind an Infoständen (mit und ohne Essensverteilung) zu finden.\n\nEine Bedingung für unsere Präsenz ist, direkt mit Festivalbetreiber*innen zu kooperieren um auf dem Veranstaltungsgelände selbst "aktiv" werden zu können.\nWir retten auf Festivals überschüssige Lebensmittel von Besucher*innen oder von Essensständen. Am Ende eines Festivals bergen wir auch zurückgelassene Dinge, die noch brauchbar sind.\
					\');"><i class="fa fa-music whatiswhaticons" aria-hidden="true"></i>
				<span class="iconlegend">Festivals</span></span></h2>
			</div>
		</div>
		';
	}

	private function withfooter()
	{
		return '
			<span class="carpetroll"><a href="/?page=content&sub=joininfo" id="joinbuttoninfootersection" class="clickable">Rette mit!</a></span>
		';
	}
}
