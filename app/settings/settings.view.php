<?php
class SettingsView extends View
{
	public function settingsInfo($fairteiler,$threads)
	{
		global $g_data;
		$out = '';
		$disabled = false;
		
		if($fairteiler)
		{
			foreach ($fairteiler as $ft)
			{
				$disabled = false;
				if($ft['type'] == 2)
				{
					$disabled = true;
				}
				
				addJs('
					$("input[disabled=\'disabled\']").parent().click(function(){
						pulseInfo("Du bist verantwortlich für Diesen Fair-Teiler!");
					});
				');
				
				$g_data['fairteiler_'.$ft['id']] = $ft['infotype'];
				$out .= v_form_radio('fairteiler_'.$ft['id'], array(
					'label' => sv('follow_fairteiler',$ft['name']),
					'desc'=>sv('follow_fairteiler_desc',$ft['name']),
					'values'=>array(
						
						array('id' => 1,'name'=>s('follow_fairteiler_mail')),
						array('id' => 2,'name'=>s('follow_fairteiler_alert')),
						array('id' => 0,'name'=>s('follow_fairteiler_none'))
					),
					'disabled' => $disabled
				));
			}
		}
		
		if($threads)
		{
			foreach ($threads as $ft)
			{
				$g_data['thread_'.$ft['id']] = $ft['infotype'];
				$out .= v_form_radio('thread_'.$ft['id'], array(
						'label' => sv('follow_thread',$ft['name']),
						'desc'=>sv('follow_thread_desc',$ft['name']),
						'values'=>array(
								array('id' => 1,'name'=>s('follow_thread_mail')),
								array('id' => 0,'name'=>s('follow_thread_none'))
						),
						'disabled' => $disabled
				));
			}
		}
		
		return v_field(v_form('settingsinfo',array(
			v_form_radio('newsletter',array(
					'desc'=>s('newsletter_desc'),
					'values'=>array(
						array('id' => 0,'name'=>s('no')),
						array('id' => 1,'name'=>s('yes'))
					)
			)),
			v_form_radio('infomail_message',array(
				'desc'=>s('infomail_message_desc'),
				'values'=>array(
				array('id' => 0,'name'=>s('no')),
				array('id' => 1,'name'=>s('yes'))
				)
			)),
			$out
		),array('submit'=>s('save'))),s('settings_info'),array('class'=>'ui-padding'));
	}
	
	public function quizSession($session,$try_count)
	{
		$subtitle = 'mit '.$session['fp'].' von maximal '.$session['maxfp'].' Fehlerpunkten leider nicht bestanden. <a href="http://wiki.lebensmittelretten.de/" target="_blank">Informiere Dich im Wiki</a> für den nächsten Versuch.';
		if($session['fp'] < $session['maxfp'])
		{
			$subtitle = 'Herzlichen Glückwunsch! mit '.$session['fp'].' von maximal '.$session['maxfp'].' Fehlerpunkten bestanden!';
		}
		addContent($this->topbar($session['name'].' Quiz', $subtitle, 'img/quiz.png'));
		$out = '';
		
		
		if($session['fp'] < $session['maxfp'])
		{
			$btn = '';
			switch($session['quiz_id'])
			{
				case 1 :
					$btn = '<a href="?page=settings&sub=upgrade/up_fs" class="button">Jetzt die Foodsaver Anmeldung abschließen!</a>';
					break;
						
				case 2:
					$btn = '<a href="?page=settings&sub=upgrade/up_bip" class="button">Jetzt die Betriebsverantwortlichen Anmeldung abschließen!</a>';
					break;
						
				case 3:
					$btn = '<a href="?page=settings&sub=upgrade/up_bot" class="button">Jetzt die Botschafter Anmeldung abschließen!</a>';
					break;
						
				default:
					break;
			}
			$out .= v_field('<p>Herzlichen Glückwunsch, Du hast es geschafft!</p><p>Die Auswertung findest Du unten.</p><p style="padding:15px;text-align:center;">'.$btn.'</p>', 'Geschafft!',array('class' => 'ui-padding'));
		}
		else
		{
			if($try_count == 1)
			{
				$out .= v_field('
					
				<p>Vielen Dank für Dein Bemühen.</p>
				<p>Doch leider hast Du mehr Fehlerpunkte gemacht als sein dürfen -
				aber kein Grund zur Sorge, das war ja erst Dein erster Versuch.</p>
				<p>Bitte informiere Dich über <a href="http://wiki.lebensmittelretten.de" target="_blank">wiki.lebensmittelretten.de</a> und dann kannst Du es noch mal versuchen.
				
				<p>Gern kannst Du ein Problem auch mit deiner/deinem BotschafterIn besprechen.</p>
				
				<p>Alles Liebe,<br />
				Dein Foodsharing Team</p>
						','Diesmal hat es leider nicht geklappt',array('class' => 'ui-padding'));
			}
			else if($try_count == 2)
			{
				$out .= v_field('
							
					<p>Vielen Dank für Dein Bemühen.</p>
					<p>Doch leider hast Du mehr Fehlerpunkte gemacht als sein dürfen -
					und das leider das zweite Mal.</p>
					<p>Womöglich solltest Du das Wiki (<a href="http://wiki.lebensmittelretten.de" target="_blank">wiki.lebensmittelretten.de</a>) genauer lesen und es dann noch ein letztes Mal versuchen. Solltest Du ein weiteres Mal zu viele Fehlerpunkte erreichen, erhältst Du leider einen Monat Lernpause, bis Du das Quiz erneut durchführen darfst.</p>
					
					<p>Gern kannst Du ein Problem auch mit deiner/deinem BotschafterIn besprechen.</p>

						<p>Alles Liebe,<br />
						Dein Foodsharing Team</p>
						','Diesmal hat es leider nicht geklappt',array('class' => 'ui-padding'));
			}
			else if($try_count == 3)
			{
				$out .= v_field('
							
					<p>Vielen Dank für Dein Bemühen - doch leider hast Du erneut mehr als 3 Fehlerpunkte gemacht.</p>
					<p>Damit möglichst viele Lebensmittel gerettet werden können, ist Zuverlässigkeit, Sicherheit und Professionalität bei den Betrieben und im Team unverzichtbar.</p>
					<p>Die Antworten, die Du gegeben hast, vermitteln dieses zum jetzigen Zeitpunkt leider nicht.</p>
					<p>Daher bekommst Du einen Monat Lernpause und dann kannst Du Dich erneut an dem Quiz versuchen.</p>
					
					<p>Gern kannst Du ein Problem auch mit deiner/deinem BotschafterIn besprechen.</p>
		
						<p>Alles Liebe,<br />
						Dein Foodsharing Team</p>
						','Diesmal hat es leider nicht geklappt',array('class' => 'ui-padding'));
			}
		}
		
		
		$i=0;
		foreach ($session['quiz_result'] as $r)
		{
			$ftext = 'hast Du komplett richtig beantwortet, Prima!';
			$i++;
			$cnt = '<div class="question">'.$r['text'].'</div>';
			
			$cnt .= v_input_wrapper('Passender Wiki-Artikel', '<a href="'.$r['wikilink'].'">'.$r['wikilink'].'</a>');
			
			$answers = '';
			$ai = 0;
			$noclicked = true;
			foreach ($r['answers'] as $a)
			{
				$ai++;
				$right = 'red';
				
				if($a['user_say'])
				{
					$noclicked = false;
				}
				
				$atext = '';
				if(!$r['noco'] && $r['percent'] == 100)
				{
					$atext = '';
					$right = 'red';
				}
				else if($a['user_say'] == $a['right'] && !$r['noco'])
				{
					$atext = '';
					$right = 'green';
					if($a['right'])
					{
						$atext = ' ist richtig!';
					}
					else
					{
						$atext = ' ist falsch, dass hast Du richtig erkannt!';
					}
				}
				else
				{
					if($a['right'])
					{
						$atext = ' wäre richtig gewesen!';
					}
					else
					{
						$atext = ' stimmt so nicht!';
					}
				}
				
				//$atext .= '<pre>'.print_r($r,true).'</pre>';
				
				$answers .= '
				<div class="answer q-'.$right.'">
					'. v_input_wrapper('Antwort '.$ai.$atext, $a['text']).'
					'. v_input_wrapper('Erklärung', $a['explanation']).'
					
				</div>';
			}
			
			if($r['userfp'] > 0)
			{
				$cnt .= v_input_wrapper('gesammelte Fehlerpunkte', $r['userfp']);
				if($r['percent'] == 100)
				{
					$ftext = ' wurde leider falsch beantwortet.';
					if(!$r['noco'] && $noclicked)
					{
						$ftext = ' wurde leider als falsch gewertet. Da Du nichts ausgewählt hast.';
					}
				}
				else
				{
					$ftext = ' hast Du leider nur zu '.(100-$r['percent']).'% richtig beantwortet.';
				}
			}
			
			$cnt .= '<div id="qcomment-'.(int)$r['id'].'">'.v_input_wrapper('Kommentar zu dieser Frage Schreiben', '<textarea style="height:50px;" id="comment-'.$r['id'].'" name="desc" class="input textarea value"></textarea><br /><a class="button" href="#" onclick="ajreq(\'addcomment\',{app:\'quiz\',comment:$(\'#comment-'.(int)$r['id'].'\').val(),id:'.(int)$r['id'].'});return false;">Absenden</a>').'</div>';
			
			$cnt .= v_input_wrapper('Antworten', $answers);
			
			$out .= v_field($cnt, 'Frage '.$i.' '.$ftext,array('class' => 'ui-padding'));
		}
		
		return $out;
		
	}
	
	public function changeMail()
	{
		return v_form_text('newmail');
	}
	
	public function changemail3($email)
	{
		return 
			v_info('E-Mail Adresse wirklich zu <strong>'.$email.'</strong> ändern?') . 
			v_form_passwd('passcheck');
	}
	
	public function settingsMumble($name)
	{
		return v_field('
				
				<table style="border-spacing: 10px;border-collapse: separate;">
				<tr>
					<td style="width:75px;">Server:</td>
					<td><strong>mumble.lebensmittelretten.de</strong></td>
				</tr>
				<tr>
					<td style="width:75px;">Port:</td>
					<td><strong>64738</strong></td>
				</tr>
				<tr>
					<td>Benutzer:</td>
					<td><strong>'.$name.'</strong></td>
				</tr>
				<tr>
					<td>Passwort:</td>
					<td><i>Dein lebensmittelretten.de Passwort</i></td>
				</tr>
				</table>
				
				', 'Deine Mumble Zugangsdaten',array('class'=>'ui-padding'));
	}
	
	public function delete_account()
	{
		addJs('
		$("#delete-account-confirm").dialog({
			autoOpen: false,
			modal: true,
			title: "'.s('delete_account_confirm_title').'",
			buttons: {
				"'.s('abort').'" : function(){
					$("#delete-account-confirm").dialog("close");
				},
				"'.s('delete_account_confirm_bt').'" : function(){
					goTo("?page=settings&deleteaccount=1&reason=" + encodeURIComponent($("#reason_to_delete").val()));
				}
			}
		});
		
		$("#delete-account").button().click(function(){
			$("#delete-account-confirm").dialog("open");
		});
	');
		$content = '
	<div style="margin:20px;text-align:center;">
		<span id="delete-account">'.s('delete_now').'</span>
	</div>
	'.v_info(s('posible_restore_account'),s('reference'));
	
		addHidden('
		<div id="delete-account-confirm">
			'.v_info(s('delete_account_confirm_msg')).'
			'.v_form_textarea('reason_to_delete').'
		</div>
	');
	
		return v_field($content, s('delete_account'),array('class'=>'ui-padding'));
	}
	
	public function foodsaver_form()
	{
		global $db;
		global $g_data;
		
		addJs('$("#foodsaver-form").submit(function(e){
		if($("#photo_public").length > 0)
		{
			$e = e;
			if($("#photo_public").val()==4 && confirm("Achtung niemand kann Dich mit Deinen Einstellungen kontaktieren. Bist Du sicher?"))
			{
	
			}
			else
			{
				$e.preventDefault();
			}
		}
	
	});');
	
		$oeff = v_form_radio('photo_public',array('desc'=>'Du solltest zumindest intern den Menschen in Deiner Umgebung ermöglichen Dich zu kontaktieren. So kannst Du von anderen Foodsavern eingeladen werden, Lebensmittel zu retten und ihr Euch einander kennen lernen.','values' => array(
				array('name' => 'Ja ich bin einverstanden, dass mein Name und mein Foto veröffentlicht werden','id' => 1),
				array('name' => 'Bitte nur meinen Namen veröffentlichen','id' => 2),
				array('name' => 'Meinen Daten nur intern anzeigen','id' => 3),
				array('name' => 'Meine Daten niemandem zeigen','id' => 4)
		)));
	
		if(isBotschafter())
		{
			$oeff = '<input type="hidden" name="photo_public" value="1" />';
		}
		$bezirkchoose = '';
		if(isOrgaTeam())
		{
			$bezirk = array('id'=>0,'name'=>false);
			if($b = getBezirk($g_data['bezirk_id']))
			{
				$bezirk['id'] = $b['id'];
				$bezirk['name'] = $b['name'];
			}
	
			$bezirkchoose = v_bezirkChooser('bezirk_id',$bezirk);
		}
	
		addJs('
		$("#plz, #stadt, #anschrift").bind("blur",function(){
	
	
			if($("#plz").val() != "" && $("#stadt").val() != "" && $("#anschrift").val() != "")
			{
				u_loadCoords({
					plz: $("#plz").val(),
					stadt: $("#stadt").val(),
					anschrift: $("#anschrift").val(),
					complete: function()
					{
						hideLoader();
					}
				},function(lat,lon){
					$("#lat").val(lat);
					$("#lon").val(lon);
				});
			}
		});
	
		$("#lat-wrapper").hide();
		$("#lon-wrapper").hide();
	');
		
		$g_data['ort'] = $g_data['stadt'];
	
		return v_quickform(s('settings'),array(
				$bezirkchoose,
				//v_form_text('name'),
				//v_form_text('nachname'),
				/*
		v_form_select('geschlecht',array('values' => array(
				array(
						'name' => 'Frau',
						'id' => 2
				),
				array(
						'name' => 'Mann',
						'id' => 1
				),
				array(
						'name' => 'Sonstiges oder Beides',
						'id' => 3
				)
		))),
		*/
				$this->latLonPicker('LatLng'),
				v_form_text('telefon'),
				v_form_text('handy'),
				v_form_textarea('about_me_public',array('desc'=>'Um möglichst transparent, aber auch offen, freundlich, seriös und einladend gegenüber den Lebensmittelbetrieben, den Foodsavern sowie allen, die bei foodsharing mitmachen wollen, aufzutreten, wollen wir neben Deinem Foto, Namen und Telefonnummer auch eine Beschreibung Deiner Person als Teil von foodsharing mit aufnehmen. Bitte fass Dich also relativ kurz, hier unsere Vorlage: http://foodsharing.de/ueber-uns Gerne kannst du auch Deine Website, Projekt oder sonstiges erwähnen, was Du öffentlich an Informationen teilen möchtest, die vorteilhaft sind.')),
				$oeff
		),array('submit'=>s('save')));
	}
	
	public function quizFailed($failed)
	{	
		
		$out = v_field($failed['body'], $failed['title'],array('class' => 'ui-padding'));
		
		return $out;
	}
	
	public function pause($days_to_wait,$desc)
	{		
		$out = v_input_wrapper('Du hast das Quiz 3x nicht bestanden', 'In '.$days_to_wait.' Tagen kannst Du es noch einmal probieren');
		
		if($desc)
		{
			$out .= v_input_wrapper($desc['title'], $desc['body']);
		}
		
		$out = v_field($out, 'Lernpause',array('class' => 'ui-padding'));
		
		return $out;
	}
	
	public function quizContinue($quiz,$desc)
	{
		$out = '';
		if($desc)
		{
			$out .= v_input_wrapper($desc['title'], $desc['body']);
		}
		
		$out .= v_input_wrapper('Du hast Das Quiz noch nicht beendet', 'Aber kein Problem, Deine Sitzung wurde gespeichert, Du kannst jederzeit die Beantwortung fortführen.');
		
		$out .= v_input_wrapper($quiz['name'], $quiz['desc']);
	
		$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',qid:'.(int)$quiz['id'].'});" href="#" class="button">Quiz jetzt weiter beantworten!</a></p>';
	
		$out = v_field($out, 'Quiz fortführen',array('class' => 'ui-padding'));
	
		return $out;
	}
	
	public function quizRetry($quiz,$desc,$failed_count,$max_failed_count)
	{
		$out = v_input_wrapper(($failed_count+1).'. Versuch', '<p>Du hast das Quiz bereits '.$failed_count.'x nicht geschafft, hast aber noch '.($max_failed_count-$failed_count).' Versuche</p><p>Viel Glück!</p>');
		
		if($desc)
		{
			$out .= v_input_wrapper($desc['title'], $desc['body']);
		}
		
		$out .= v_input_wrapper($quiz['name'], $quiz['desc']);
		
		$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',qid:'.(int)$quiz['id'].'});" href="#" class="button">Quiz jetzt starten</a></p>';
		
		$out = v_field($out, 'Du musst noch das Quiz bestehen!',array('class' => 'ui-padding'));
		
		return $out;
	}
	
	public function confirmBot($cnt)
	{
		$out = '';
		if($cnt)
		{
			$out .= $cnt['body'];
		}
	
		$out .= v_form('confirmFs', array(
					
		),array( 'submit' => 'Bestätigen'));
	
		$out = v_field($out, $cnt['title'],array('class' => 'ui-padding'));
	
		return $out;
	}
	
	public function confirmBip($cnt)
	{
		$out = '';
		if($cnt)
		{
			$out .= $cnt['body'];
		}
	
		$out .= v_form('confirmFs', array(
					
		),array( 'submit' => 'Bestätigen'));
	
		$out = v_field($out, $cnt['title'],array('class' => 'ui-padding'));
	
		return $out;
	}
	
	public function confirmFs($cnt)
	{
		$out = '';
		if($cnt)
		{
			$out .= $cnt['body'];
		}
		
		$out .= v_form('confirmFs', array(
			
		),array( 'submit' => 'Bestätigen'));
		
		$out = v_field($out, $cnt['title'],array('class' => 'ui-padding'));
		
		return $out;
	}
	
	public function quizIndex($quiz,$desc)
	{
		$out = '';
		if($desc)
		{
			$out .= v_input_wrapper($desc['title'], $desc['body']);
		}
		
		$out .= v_input_wrapper($quiz['name'], nl2br($quiz['desc']));
		
		$out .= '<p><a onclick="ajreq(\'startquiz\',{app:\'quiz\',qid:'.(int)$quiz['id'].'});" href="#" class="button">Quiz jetzt starten</a></p>';
		
		$out = v_field($out, 'Du musst noch das Quiz bestehen!',array('class' => 'ui-padding'));
		
		return $out;
	}
}