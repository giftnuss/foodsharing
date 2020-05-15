# Release Apfelsine Mai 2020 

<img class="pageImageFloatRight" src="./img/releasenotes/mai-2020-releasenotes.png">
# foodsharing im Browser

Mit dem Release der neuen Software-Version sind weitere Funktionen verfügbar. Allerdings wirst du gar nicht alle Änderungen merken, auch wenn einige wirklich viel Arbeit gemacht haben. Zum Beispiel sind wir Fehlermeldungen angegangen, 
haben den Software-Code modernisiert und Layout-Verbesserungen vorgenommen. Im Changelog findest du viele Einträge, die 'refactoring' oder 'bugfixing' betreiben, und relativ wenig neue Features/Funktionen - Das ist der aktuelle Weg der aktiven Programmierer\*innen, um das Arbeiten an der Seite für neue Menschen attraktiv zu machen. Aktuell ist unser ehrenamtliches Team sehr ausgelastet und freut sich stets über Unterstützung. Wenn es eine neue Funktion gibt, die du gerne programmiert sehen willst, ist es am besten, du findest selbst Entwickler\*innen, die Lust haben, dieses zu implementieren.

Die großen Herausforderungen seit dem letzten Release waren die Karte (wo wir einen neuen Kartenserver finden mussten, was in der Größe von foodsharing nicht so einfach ist) und die Anzahl der Mails, die durch die Seite 
verschickt wurden (es sind sehr, sehr viele - unser Mailhoster hat dankenswerterweise mit uns eine Übergangslösung gefunden, bis wir wieder weniger E-Mails pro Minute verschicken).
Die Ausarbeitung hat viel Zeit und Energie gekostet. Wir hoffen, dass wir uns jetzt wieder auf schönere Themen konzentrieren können. :grin: 

Wir haben [Notizen im Changelog](https://foodsharing.de/?page=content&sub=changelog) gesichtet, diskutiert und formuliert. Nun haben wir bündig zusammengefasst, was sich für dich ändert, 
wenn du Foodsharer\*in oder Foodsaver\*in, BV oder BOT bist.

Die Release Notes haben jetzt einen eigenen Ort. Du findest sie oben beim Informations-i unter dem Begriff "Was ist neu?" (Referenz !1474)

## Foodsharer\*in und Foodsaver\*in

* Das Registrierungsformular für neue Foodsharer\*innen wurde vollständig neu entwickelt. Da wir beim Beheben von Fehlern mit alten Technologien immer wieder an Grenzen gestoßen sind, wurde das Formular in eine Mehr-Seiten-Variante mit aktueller Technologie (vue js) neu programmiert.
Dabei haben wir an den wichtigen Stellen, umfangreiche Überprüfungen der Eingaben eingebaut. Die Eingabe vom Geburtsdatum funktioniert jetzt deutlich einfacher. Für die Eingabe einer optionalen Handynummer wurde eine Funktion zur Auswahl des Ländercodes eingebaut.
Die Eingabe der Adresse oder das Hochladen vom Profil-Foto haben wir nicht eingebaut, da es freiwillig ist und auch später in den Einstellungen nachgeholt werden kann.
(Referenz: !1401 )

* Wir sind zu einem anderen Kartenanbieter (Geoapify) gewechselt. Die Gelegenheit haben wir genutzt, um die Kartendarstellung auf eine modernere Technologie umzustellen, wodurch die Karte jetzt mit deutlich höherer Auflösung dargestellt wird.
(Referenz: !1405 !1355 )

* Wenn du neu bei foodsharing bist und das Quiz bereits bestanden hast, aber du noch keinen Stammbezirk gewählt hast, bekommst du einen Hinweis und wirst zur Bezirksauswahl umgeleitet. 
(Referenz: !1123 )

<img class="pageImageFloatLeft" src="./img/releasenotes/mai-2020-store-marker.png">
* Eine der deutlichsten Änderungen in diesem Release betrifft Foodsaver\*innen und BVs. Wenn man das Symbol der Betriebsliste oben (beim Einkaufswagen-Symbol) anwählt, wird jetzt angezeigt, wenn Handlungsbedarf besteht. Es gibt ein rotes Ampellicht, wenn es heute oder morgen freie Slots gibt; ein oranges Licht für leere Slots in ‘heute+1’ bis ‘heute+3’ Tagen und ein gelbes Licht für leere Slots in ‘heute+3’ bis ‘heute+5’ Tagen.
Beispiel: Der nächste zu füllende Slot ist in 4 Tagen. Dann wird ein gelbes Warnlicht angezeigt – es sei denn, du hast bereits einen Slot aus der Reihe übernommen. Dann wird dieses Datum für deine Warnampel nicht berücksichtigt.
Wenn in der näheren Zeit keine freien Slots übrig sind, gibt es auch keine Ampel-Anzeige. :-)
Die ampelfarbenen Marker werden auch erklärt, wenn man mit der Maus über das Ampelsymbol fährt.
(Referenz: !1106 !1133 !1331 !1502)

* Du kannst jetzt auf dem Dashboard nach Betriebsupdates, E-Mails etc. filtern. Dazu sind die neuen Icons da.
(Referenz: !735)

* Du kannst jetzt einstellen, dass du für Chat-Nachrichten Push-Benachrichtigungen über den Browser (auch auf dem Smartphone) erhältst. Diese aktivierst du über die Einstellungen oder die Infobox auf dem Dashboard. (Referenz: !734 !1444 )

* Benutzer\*innen, die sich sechs Monate lang nicht eingeloggt haben, kriegen keine E-Mails mehr über Forumsbeiträge. Das heißt, wir sparen hier etwas E-Mails und du bekommst weniger automatische Mails, wenn du gerade inaktiv bist. (Referenz: !1385 )

* Außerdem haben wir ein neues Feld "Kurzbeschreibung" auf dem Profil eingefügt. Hier kannst Du dich (nur für angemeldeten Benutzer sichtbar) selbst beschreiben.
(Referenz: !1145 )

* Wenn du dich für eine Arbeitsgruppe oder einen Bezirk bewirbst und abgelehnt wirst, kannst du dich danach erneut bewerben. (Abgelehnte Anträge werden nun gelöscht. Referenz: !1277 )

* Auf dem Handy (Browserversion) war es eine Zeit lang nicht möglich, Betrieben mit langen Beschreibungen beizutreten. Das ist jetzt gelöst.
(Referenz: !1378 )

* Auch das Problem, dass Untermenüs des Burgermenüs in der mobilen Version (Browserversion) nicht vollständig lesbar waren, ist nun behoben. 
(Referenz: !1411 )

* Auf dem Handy (Browserversion) werden jetzt die letzte Abholung und der letzte Teambeitritt auf der Teamliste angezeigt.
(Referenz: !1335 )

* Wenn die interne Adresse einer empfangenen E-Mail nicht gefunden werden kann, wird die Mail nicht mehr in 'lost@foodsharing' einsortiert. Stattdessen wird eine automatische Antwort gesendet, die besagt, dass die Adresse nicht gefunden werden konnte.

Der Text dieser Email ist noch etwas kurz und könnte durch etwas Schöneres ersetzt werden. Jede Idee ist willkommen :)
(Referenz: !1346 )

* Aus deiner foodsharing-Mailbox können Mails nun auch mit Leerzeichen versendet werden. Das heißt, es macht für den Versand nichts mehr aus, wenn eine Mailadresse versehentlich mit Leerzeichen eingetragen wird.
(Referenz: !1372 )

* Wir haben die [Statistik-Seite](https://foodsharing.de/statistik) um Informationen erweitert. (Referenz: !1351 )

* Wir haben einen grafischen Fehler behoben, durch den zum Beispiel Links in der Betriebsbeschreibung über den Seitenrand hinausreichten. (Referenz: !1269 )

* Die Darstellung vom Dashboard wurde für Smartphones oder kleine Bildschirme verbessert. (Referenz: !1494) 

* Nur als Info: Es kam ab und zu vor, dass Foodsaver\*innen ihre Accounts gelöscht haben, als sie die Datenschutzrichtlinie akzeptiert haben. Daher wurden nun die Farbe und die Formulierung angepasst, sodass klarer ist, dass bei Ablehnung der Richtlinie der Account gelöscht wird.
(Referenz: !1318)

## Betriebsverantwortliche 

* Wir haben neben vielen kleinen Fehlern einen großen ausgemerzt, bei dem Mailboxen nicht erstellt wurden.
Die beeinträchtigten FS können jetzt auch wieder Visitenkarten erstellen. (Referenz: !1356 )

* Deine persönliche foodsharing-Mailadresse siehst du nun auf deinem Profil. Die Mailadresse ist nur für dich und Personen mit Orga-Rechten sichtbar.(Referenz: !1387 )

<img class="pageImageFloatRight" src="./img/releasenotes/mai-2020-store-fs-verified.png">
* Visitenkarten mit langen Bezirksnamen werden jetzt auch wieder korrekt erstellt. (Referenz: !1362 )

* Bewerben sich FS als Abholer\*innen in einem Betrieb, siehst du nun an einem neuen Symbol, ob die Person bereits verifiziert ist. (Referenz: !1294 )

* Der Button zum Anlegen neuer Betriebe passt jetzt besser ins Design. (Referenz: !1282 !1339 )

## Botschafter\*innen

* Bisher wurden bei ausgehenden Mails keine Zeilenumbrüche angezeigt. Dies ist jetzt behoben. (Referenz: !1317 !1344 )

* In der Liste bei der Ausweiserstellung sind die Namen der Foodsaver\*innen nun alphabetisch pro Bezirk sortiert. Die Auflistung der AGs wurde entfernt.  (Referenz: !1310)

<img class="pageImageFloatLeft" src="./img/releasenotes/mai-2020-choosing-mails-sending-forum.png">
* Als BOT kannst du jetzt bei unmoderierten Foren wie dem Europaforum auswählen, ob alle in der Region und damit im Forum per Mail über neue Beiträge informiert werden sollen. 
Das sollte unsere Maillast etwas verringern. (Referenz: !1233)


## AG-Admins und Orga
* Es ist jetzt wieder möglich, Blog-Beiträge zu veröffentlichen, zu bearbeiten und zu löschen. (Referenz: !1349 )

## Orga

* Personen mit Orga-Rechten können nun die Rollen anderer Benutzer\*innen ändern. (Referenz: !1322 !1323 )

* Beiträge auf Fair-Teiler-Pinnwänden können jetzt gelöscht werden. Admins der Arbeitsgruppen können wieder die Wall für Bewerber sehen. (Referenz: !1359 )

---
## App

Die folgenden Änderungen gelten für die Android-App. Leider gibt es aktuell keine Neuerungen für die iOS-Version. Wenn du hier unterstützen willst, melde dich unter [it@foodsharing.network](mailto:it@foodsharing.network?subject=IOS-Ich-will-helfen). (Oder komm direkt über [slackin.yunity.org](slackin.yunity.org) in den Kanal `#fs-dev-ios`.)


#### Was ist neu?
* Du kannst nun auch Text von jeder anderen App über foodsharing teilen. Diese Funktion kannst du ganz einfach nutzen, wenn du über "Teilen" die foodsharing-App auswählst. (Referenz: android!195)

* Die Pinnwände von Fair-Teilern werden jetzt auch in der App angezeigt und du kannst mit der App neue Einträge erstellen. Leider ist es momentan noch nicht möglich neue Bilder über die App hochzuladen, aber das schaffen wir bestimmt bis zum nächsten Release. (Referenz: android!193)

* Wenn du einen Essenskorb erstellt hast, kannst du das Foto auch später jederzeit ändern. Dabei kannst du entscheiden, ob du ein Foto aus deiner Galerie wählen oder ein neues machen möchtest. Essenskörbe werden in der Liste jetzt mit Foto dargestellt. 
  Das Design der Essenskörbe ist nun angepasst an die Website. Das heißt, die aktuelle Anzahl der Anfragen wird angezeigt, du kannst eine Anfrage für den Essenskorb stellen, deine bisherige Anfrage zurückziehen oder eine Nachricht an den/die Ersteller\*in schreiben.
  Wenn du der Standortbestimmung durch die App zustimmst, wird dir auch die Distanz zum Essenskorb angezeigt. Auch die Gruppierung der Icons wurde optimiert, damit es nicht mehr zu Icon-Überschneidungen kommt.
(Referenz: android!181 android!182 android!177 android!180 android!179 android!183 android!190 )
  <img src="./img/releasenotes/mai-2020-android-essenskorb-erstellen.jpg">
  <img src="./img/releasenotes/mai-2020-android-essenskoerbe.jpg">
  <img src="./img/releasenotes/mai-2020-android-karte.jpg">

* Die Status-Updates werden jetzt in den Profilen angezeigt und du kannst in deinem Profil ein neues Status-Update posten.

* Im Burgermenü der App gibt es jetzt einen direkten Link zur Website, damit du schnell hin und her springen kannst. 
Außerdem kannst du nun ebenfalls über das Burgermenü die foodsharing-App mit nur einem Klick weiterempfehlen. (Referenz: android!198 android!199 android!185)
<img class="pageImageFloatLeft" src="./img/releasenotes/mai-2020-android-teilen.png">

#### Behobene Fehler
* Vermutlich ist dir schon aufgefallen, dass du schnell wieder aus der App ausgeloggt wirst. Wir haben hier ein paar Optimierungen vorgenommen, sodass dies nicht mehr passieren sollte (Referenz: !1496)
* Wir haben den Bug behoben, dass eine Nachricht erst nach dem Neuladen der Seite als gelesen markiert wird. (Referenz: android!192)
* Die Karte sollte jetzt nicht mehr im Meer bei Afrika an der N 0.0 E 0.0 Koordiante zentriert sein. (Referenz: android!211)

<img class="pageImageFloatRight" src="./img/releasenotes/mai-2020-android-mr-192.png">



---

## Danke für deine Aufmerksamkeit
Wir hoffen, die Veränderungen sind für dich eine Bereicherung.

Danke an die fleißigen Programmierer\*innen der IT, die das alles durch ehrenamtliche Arbeit ermöglicht haben! 

Wenn etwas unklar geblieben ist, schau gerne im [Changelog](https://foodsharing.de/?page=content&sub=changelog) nach und klickt auf die Ausrufezeichen (!) und Rauten (#), die du dort findest. Und wenn dann noch Fragen sind, frag gerne [it@foodsharing.network](mailto:it@foodsharing.network?subject=Frage-zu-Release-Notes).

Wenn dich interessiert, was in der letzten Zeit noch passiert ist: [Hier gibt es mehr von foodsharing](https://foodsharing.de/news#).

PS: Vielleicht bist ja du, werte lesende Person, ein\*e begeisterte\*r Nutzer\*in der App oder sogar ein\*e Programmierer\*in und/oder ein lernfähiger Mensch ohne Programmierkenntnisse mit etwas Zeit und dem Willen, sich einzubringen. 
Dann schau doch mal in unseren [Aufruf zur Mitarbeit](https://devdocs.foodsharing.network/it-tasks.html) und [melde dich bei uns](mailto:it@foodsharing.network?subject=Ich-will-helfen). Wir freuen uns, von dir zu hören.

Weiterhin frohes Retten!
Für das Team: Laura, Jonathan und Christian
