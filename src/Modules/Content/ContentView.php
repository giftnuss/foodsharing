<?php

namespace Foodsharing\Modules\Content;

use Foodsharing\Modules\Core\View;

class ContentView extends View
{
	public function simple($cnt)
	{
		return '
		<div class="page ui-padding ui-widget-content corner-all">
			<h1>' . $cnt['title'] . '</h1>
			' . $cnt['body'] . '
		</div>';
	}

	public function releaseNotes($cnt)
	{
		return '
		<div class="page ui-padding ui-widget-content corner-all">
			' . $cnt['changelog'] . '
			<h1>' . $cnt['title'] . '</h1>
			' . $cnt['body'] . '
		</div>';
	}

	public function partner($cnt)
	{
		return '
		<div class="page ui-padding ui-widget-content corner-all">
			<h1>' . $cnt['title'] . '</h1>
			' . $cnt['body'] . '
		</div>';
	}

	public function impressum($cnt)
	{
		return '
		<div class="page ui-padding ui-widget-content corner-all">
			<h1>' . $cnt['title'] . '</h1>
			' . $cnt['body'] . '
		</div>';
	}

	public function about($cnt)
	{
		return '
		<div class="page ui-padding ui-widget-content corner-all">
			<h1>' . $cnt['title'] . '</h1>
			' . $cnt['body'] . '
		</div>';
	}

	public function joininfo()
	{
		return '
		<div class="page ui-padding ui-widget-content corner-all">
			<h1>Mitmachen - Unsere Regeln</h1>
			<h3>Schön, dass Du bei foodsharing mitmachen willst!</h3>
			<p>Da Lebensmittel uns am Leben halten, wollen wir auch respektvoll mit ihnen umgehen. Damit bei uns alles optimal läuft, haben wir im Laufe der Jahre einige Verhaltensregeln definiert. Lebensmittel an andere weiterzugeben, ist eine sehr menschliche, aber auch verantwortungsvolle Sache. Grundsätzlich gilt immer: „Nichts an andere weitergeben, was man selbst nicht mehr essen würde“. Bitte lies nun die foodsharing-Etikette einmal durch!<br><b>Am Ende dieser Seite kannst du dich als Foodsharer registrieren.</b></p>
			<h3>Für Unternehmen und Betriebe</h3>
			<p>Gemeinsam mit foodsharing können Sie sich dafür einsetzen, dass aussortierte und unverkäufliche Lebensmittel eine sinnvolle Verwendung erfahren, statt weggeworfen zu werden. Mehr Information dazu finden Sie <a href="/fuer-unternehmen">hier.</a></p>'
			. $this->v_utils->v_field('
			<div class="reddot">
			<h5><span>1</span> Sei ehrlich</h5>
			<p>Wir alle, die foodsharing entwickelt haben und die Webseite betreiben, nehmen unsere Aufgabe sehr ernst. Wir befolgen eine Reihe von Gesetzen und Auflagen. Sei auch du bitte ehrlich beim Ausfüllen deiner Daten und bei allen anderen Beiträgen, die du auf der Plattform machst, z.B. bei Essenskörben und Forumsbeiträgen.</p>
			<h5><span>2</span> Beachte die Regeln und den <a href="https://wiki.foodsharing.de/Ratgeber" target="_blank" rel="noopener noreferrer nofollow">Ratgeber</a> für die Weitergabe von Lebensmitteln</h5>
			<p>Wir weisen ausdrücklich darauf hin, dass wir das Anbieten und Teilen bestimmter Lebensmittel und anderer Waren aus rechtlichen Gründen nur unter bestimmten Auflagen gestatten. Das betrifft insbesondere leicht verderbliche Lebensmittel wie roher Fisch, rohes Fleisch, rohe Eierspeisen und zubereitete Lebensmittel. Generell achten wir mit allen Sinnen darauf, dass die Lebensmittel noch genießbar sind. Alle Details dazu finden sich in den <a href="https://wiki.foodsharing.de/Hygieneregeln" target="_blank" rel="noopener noreferrer nofollow">foodsharing-Hygieneregeln</a>. Hinweise finden sich auch im <a href="https://wiki.foodsharing.de/Ratgeber" target="_blank" rel="noopener noreferrer nofollow">Ratgeber</a>.</p>
			<p>Die Weitergabe von Medikamenten (auch homöopathischen Medikamenten) ist bei foodsharing ausgeschlossen. Auch Kleidung, Kosmetika, Haushaltschemie, Spielzeug und andere Non-Food-Produkte können über foodsharing nicht getauscht oder geteilt werden. Die Plattform foodsharing behält sich vor, derartige Angebote zu löschen.</p>
			<h5><span>3</span> Sei verantwortungsvoll</h5>
			<p>30<span style="white-space:nowrap">&thinsp;</span>% aller Lebensmittel landen im Müll. Damit soll nun endlich Schluss sein. Wir möchten nichts mehr wegwerfen! Wir wollen verantwortungsvoll mit Lebensmitteln umgehen und freuen uns, dass Du mitmachst.</p>
			<h5><span>4</span> Sei zuverlässig</h5>
			<p>In vielen Städten gibt es Fairteiler an denen Lebensmittel geteilt werden. Ihr könnt euch dort oder an anderen neutralen Orten treffen zum Teilen treffen. Wenn ihr Lebensmittel übergeben oder übernehmen wollt, seid bitte zuverlässig und pünktlich, lasst keinen im "Regen" stehen.</p>
			<h5><span>5</span> Mach Vorschläge</h5>
			<p>Wir wollen uns weiterentwickeln, immer besser werden. Dazu brauchen wir Euch mit vielen guten Ideen und Tipps. Die schickt Ihr an <a href="mailto:info@foodsharing.de">info@foodsharing.de</a></p>
			</div>', 'foodsharing Etikette', ['class' => 'ui-padding']) . '
			<p class="buttons"><br><a href="?page=register" style="font-size:180%;" class="button">Jetzt registrieren!</a><br></p>
		</div>
		';
	}
}
