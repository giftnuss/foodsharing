<?php
$g_lang = [];
$g_lang['geb_datum'] = 'Geburtsdatum';
$g_lang['name'] = 'Name';
$g_lang['add'] = 'hinzufügen'; // StoreUserView
$g_lang['nachname'] = 'Nachname';
$g_lang['subject'] = 'Betreff'; // at least EmailControl
$g_lang['message'] = 'Nachricht';
$g_lang['address'] = 'Adresse';
$g_lang['edit'] = 'bearbeiten'; // StoreControl
$g_lang['desc'] = 'Beschreibung';
$g_lang['description'] = 'Beschreibung';
$g_lang['plz'] = 'Postleitzahl (automatisch aus Karte oben!)';
$g_lang['email'] = 'E-Mail-Adresse';
$g_lang['attachement'] = 'Anhang'; // EmailControl
$g_lang['about_me_public'] = 'Öffentliche Kurzbeschreibung'; // SettingsView
$g_lang['about_me_intern'] = 'Kurzbeschreibung'; // SettingsView
$g_lang['telefon'] = 'Festnetznummer';
$g_lang['handy'] = 'Handynummer';
$g_lang['anschrift'] = 'Straße und Hausnummer (automatisch aus Karte oben, ggf. anschließend korrigieren!)';
$g_lang['geschlecht'] = 'Geschlecht';
$g_lang['botschafter'] = 'Botschafter';
$g_lang['options'] = 'Optionen'; // FoodSharePointControl FoodSharePointView foodSharePoint.twig
$g_lang['picture'] = 'Bild'; // BlogView and maybe more
$g_lang['message'] = 'Nachricht';
$g_lang['parent_id'] = 'Elternbezirk'; // at least XhrMethods and RegionAdminControl
$g_lang['fs_id'] = 'Deine foodsharing-ID';
$g_lang['lat'] = 'Längengrad';
$g_lang['lon'] = 'Breitengrad';
$g_lang['stadt'] = 'Deine Stadt oder Gemeinde'; // DashboardControl
$g_lang['bezirk'] = 'Bezirk';
$g_lang['betrieb'] = 'Kooperationsbetrieb/e';
$g_lang['foodsaver'] = 'Foodsaver';
$g_lang['active'] = 'Aktiviert'; // TODO check this
$g_lang['date'] = 'Datum';
$g_lang['mailbox_name'] = 'Mailbox-Name'; // XhrMethods
$g_lang['rolle'] = 'Benutzerrolle'; // FoodsaverView
$g_lang['last_login'] = 'Letzter Login';
$g_lang['datetime'] = 'Zeitpunkt'; // ReportView
$g_lang['title'] = 'Überschrift';
$g_lang['body'] = 'Nachricht';
$g_lang['forum'] = 'Forum';
$g_lang['status'] = 'Aktueller Status';
$g_lang['info'] = 'Informationen';
$g_lang['photo'] = 'Foto';
$g_lang['ort'] = 'Stadt/Gemeinde (automatisch aus Karte oben!)';
$g_lang['daterange'] = 'Zeitraum';
// $g_lang['regions'] = 'Bezirke'; TODO check if used anywhere else
$g_lang['message_text_to_group_admin_workgroup'] = 'Die Region/AG <b>{groupName}</b> hat <b>keinen</b> Botschafter/Admin mehr.<br><br><br>Sie besitzt folgende ID-Struktur:<br>{idStructureList}<br><br>Die ID lautet: {groupId}<br><br>URL: <a href="https://foodsharing.de/?page=bezirk&bid={groupId}&sub=forum" target="_blank">Klicke hier um zur zur Region/AG zu gehen</a>';
// === more hardcoded form data below === //
// BasketView + BasketXhr
$g_lang['weight'] = 'Geschätztes Gewicht';
$g_lang['food_type'] = 'Welche Arten von Lebensmitteln sind dabei?';
$g_lang['food_art'] = 'Was trifft auf die Lebensmittel zu?';
$g_lang['fetchstate'] = 'Hat alles gut geklappt?';
$g_lang['contact_type'] = 'Wie möchtest du kontaktiert werden?';
$g_lang['lifetime'] = 'Wie lange soll dein Essenskorb gültig sein?';
// BlogView
$g_lang['teaser'] = 'Teaser'; // BlogView
$g_lang['bezirk_id'] = 'Für welche Region ist der Artikel relevant?'; // BlogView
// BusinessCardView
$g_lang['opt'] = 'Optionen'; // BusinessCardView
// ContentControl
$g_lang['body'] = 'Inhalt'; // ContentControl (and maybe others)
// EmailControl
$g_lang['testemail'] = 'Test-E-Mail-Adresse'; // EmailControl
$g_lang['mailbox_id'] = 'Absender-E-Mail-Adresse'; // EmailControl
// EventView
$g_lang['location_name'] = 'Veranstaltungsort / Konferenzraum'; // EventView
$g_lang['online_type'] = 'Findet das Event offline oder online statt?'; // EventView
$g_lang['dateend'] = 'Enddatum'; // EventView
// FoodsaverView
$g_lang['orgateam'] = 'Bundesweite Orga'; // FoodsaverView
$g_lang['fs_id'] = 'foodsharing-ID'; // FoodsaverView
$g_lang['position'] = 'Position bei foodsharing (öffentlich)'; // FoodsaverView
// FoodSharePoint
$g_lang['infotype'] = 'Benachrichtigung'; // follow/unfollow modal
$g_lang['fsp_bezirk_id'] = 'In welchem Bezirk ist der Fairteiler?'; // FoodSharePoint
$g_lang['fspmanagers'] = 'Foodsaver, die Ansprechpartner für den Fairteiler sind'; // FoodSharePoint
// LoginView
$g_lang['pass1'] = 'Dein neues gewünschtes Passwort'; // LoginView
$g_lang['pass2'] = 'Passwortwiederholung'; // LoginView
// $g_lang['login_location'] = 'Deine Adresse'; // TODO check if used anywhere else
// MapView
$g_lang['want_to_fetch'] = 'In diesem Team würde ich gerne helfen.'; // MapView
$g_lang['specials'] = 'Besonderheiten'; // MapView
// MessageView
$g_lang['compose_recipients'] = 'Empfänger'; // MessageView
$g_lang['compose_body'] = 'Nachricht'; // MessageView
// SettingsView
$g_lang['newsletter'] = 'Newsletter'; // SettingsView
$g_lang['infomail_message'] = 'Benachrichtigung über Chat-Nachrichten auf foodsharing'; // SettingsView
// $g_lang['comment'] = 'Anregungen, Kritik und Kommentare'; // this looks very unused
$g_lang['newmail'] = 'Gib hier deine neue E-Mail-Adresse ein'; // SettingsView
$g_lang['passcheck'] = 'Bestätige die Änderung bitte mit deinem Passwort'; // SettingsView
$g_lang['sleep_status'] = 'Dein aktueller Status'; // SettingsView
$g_lang['sleep_msg'] = 'Hier kannst du eine kurze Nachricht hinterlassen, warum du gerade keine Zeit hast.'; // SettingsView
$g_lang['homepage'] = 'Deine Webseite'; // SettingsView
// StoreView (pickup time management)
$g_lang['verantwortlicher'] = 'Verantwortliche Mitglieder'; // StoreUserControl
$g_lang['storemanagers'] = 'Betriebsverantwortliche'; // StoreUserControl
$g_lang['team_status'] = 'Teamstatus'; // StoreUserView
$g_lang['time'] = 'Uhrzeit'; // StoreView
$g_lang['fetchercount'] = 'Anzahl der Abholer*innen'; // StoreView
// StoreView (betrieb_form)
$g_lang['kette_id'] = 'Betriebskette';
$g_lang['betrieb_kategorie_id'] = 'Kategorie';
$g_lang['betrieb_status_id'] = 'Status';
$g_lang['store_status_impact_explanation'] = 'Bitte aktualisiere nach jedem Betriebskontakt (<a href="https://wiki.foodsharing.de/Kooperationsaufbau_-_Checkliste" target="_blank">Wiki: Kooperationsaufbau - Checkliste</a>) den Status (<a href="https://wiki.foodsharing.de/Betrieb" target="_blank">Wiki: Betrieb</a>) des Betriebes, so dass der Stand der Ansprache für alle sichtbar ist.<br><br>Nur kooperationswillige Betriebe werden später oben im Betriebsmenü der Navigationsleiste angezeigt.';
$g_lang['ansprechpartner'] = 'Betriebsansprechpartner (Filialleiter etc.)';
$g_lang['fax'] = 'Fax';
$g_lang['lebensmittel'] = 'Welche Lebensmittel dürfen abgeholt werden?';
$g_lang['begin'] = 'Beginn der Kooperation';
$g_lang['besonderheiten'] = 'Besonderheiten';
$g_lang['public_info'] = 'Öffentliche Infos zum Betrieb';
$g_lang['public_time'] = 'Ungefähre Tageszeit der Abholung';
$g_lang['first_post'] = 'Erster Pinnwandeintrag';
$g_lang['ueberzeugungsarbeit'] = 'War es einfach, eine/n Verantwortliche/n zu überzeugen, mit foodsharing zu kooperieren?';
$g_lang['presse'] = 'Ist der Betrieb/Laden/Verein bereit, bei der Presse und foodsharing.de genannt zu werden?';
$g_lang['sticker'] = 'Ist der Betrieb/Laden/Verein etc. gewillt, einen Sticker beim Eingang oder anderswo sichtbar anzubringen?';
$g_lang['prefetchtime'] = 'Wie viele Wochen im Voraus können sich Foodsaver mittels automatischer Slots eintragen?';
$g_lang['abholmenge'] = 'Wie viel Kilogramm werden pro Abholung ungefähr mitgenommen?';
// WorkGroup
$g_lang['member'] = 'Mitglieder';
$g_lang['leader'] = 'Gruppen-Admins';
$g_lang['apply_type'] = 'Wer kann sich für diese Gruppe eintragen?';
$g_lang['banana_count'] = 'Wie viele Vertrauensbananen braucht ein Mitglied?';
$g_lang['fetch_count'] = 'Wie viele Abholungen sollte ein Bewerber gemacht haben?';
$g_lang['week_num'] = 'Seit wie vielen Wochen sollte ein Bewerber schon dabei sein?';
