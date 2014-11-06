<?php
global $g_lang;


addBread('Botschafter werden');

$showform = true;

if(isset($_GET['form']) && $_GET['form'] == 'botschafter')
{
	$rolle = 3;
	
	if($_GET['form'] == 'foodsaver')
	{
		$rolle = 2;
	}
	
	if(submitted())
	{
		global $g_data;
		$g_data = $_POST;
		
		$check = true;
		if(!isset($_POST['photo_public']))
		{
			$check = false;
			error('Du musst dem zustimmen das wir Dein Foto veröffentlichen dürfen');
		}
		
		if(empty($_POST['about_me_public']))
		{
			$check = false;
			error('Deine Kurzbeschreibung ist leer');
		}
		
		if(!isset($_POST['tel_public']))
		{
			$check = false;
			error('Du musst dem zustimmen das wir Deine Telefonnummer veröffentlichen');
		}
		
		if(!isset($_POST['aufgaben_botschafter']))
		{
			$check = false;
			error('Bitte bestätige das Du die Aufgaben der Botschafter gelesen hast und sie akzeptierst');
		}
		
		if(!isset($_POST['datenschutz']))
		{
			$check = false;
			error('Bitte akzeptiere die Datenschutzerklärung');
		}
		
		if((int)$_POST['bezirk'] == 0)
		{
			$check = false;
			error('Du hast keinen Bezirk gewählt in dem Du Botschafter werden möchtest');
		}
		
		if($check)
		{
			$data = unsetAll($_POST, array('photo_public','new_bezirk'));
			$db->updateFields($data, 'foodsaver', fsId());
		
			$db->add_upgrade_request(array(
					'foodsaver_id' => fsId(),
					'rolle' => $rolle,
					'bezirk_id' => $_POST['bezirk'],
					'time' => date('Y-m-d H:i:s'),
					'data' => json_encode($_POST)
			));
			
			info('Danke Dir für Deine Motivation mehr Verantwortung zu übernehmen! Die Anfrage wird schnellstmöglich vom bundesweiten Orga-Team bearbeitet.');
			$g_data = array();
			$showform = false;
		}
	}
	
	if($showform)
	{
	addJs('$("#upBotsch").submit(function(ev){
			
			check = true;
			if($("#bezirk").val() == 0)
			{
				check = false;
				error("Du musst einen bezirk ausw&auml;hlen");
			}
	
			if(!check)
			{
				ev.preventDefault();	
			}
			
		});');
		addContent(
			v_form('upBotsch', array( v_field(
					v_bezirkChooser('bezirk',getBezirk(),array('label'=>'In welcher Region möchtest Du Botschafter werden?')).
				'<div style="display:none" id="bezirk-notAvail">'.v_form_text('new_bezirk').'</div>'.
				v_form_select('time',array('values'=>array(
						array('id'=>1,'name' => '3-5 Stunden'),
						array('id'=>2,'name' => '5-8 Stunden'),
						array('id'=>3,'name' => '9-12 Stunden'),
						array('id'=>4,'name' => '13-15 Stunden'),
						array('id'=>5,'name' => '15-20 Stunden')
				))).
				v_form_radio('photo_public',array('required'=>true,'values' => array(
					array('id'=>1,'name'=>'Ich bin einverstanden das Mein Name und Mein Foto veröffentlicht werden'),
					array('id'=>2,'name'=>'Bitte NUR meinen Namen veröffentlichen')
				))).
				v_form_checkbox('tel_public',array('desc'=>'Neben Deinem vollem Namen (und eventuell Foto) ist es für
										Händler, Foodsharing-Freiwillge, Interessierte und die Presse
										einfacher und direkter, Dich neben der für Deine
										Region/Stadt/Bezirk zugewiesenen Botschafter-Emailadresse (z.B. mainz@lebensmittelretten.de)
										über Deine Festnetz- bzw. Handynummer zu erreichen. Bitte gebe
										hier alle Nummern an, die wir veröffentlichen dürfen und am
										besten noch gewünschte Anrufzeiten.','required'=>true,'values' => array(
					array('id'=>1,'name'=>'Ich bin einverstanden das Meine Telefonnummer veröffentlicht wird.')
				))).
				v_form_textarea('about_me_public',array('desc'=>'Um möglichst transparent, aber auch offen, freundlich, seriös
										und einladend gegenüber den Lebensmittelbetrieben, den
										Foodsavern sowie allen, die bei foodsharing mitmachen wollen,
										aufzutreten, wollen wir neben Deinem Foto, Namen und
										Telefonnummer auch eine Beschreibung Deiner Person als Teil von
										foodsharing mit aufnehmen. Bitte fass Dich also relativ kurz,
										hier unsere Vorlage: <a target="_blank"	href="http://www.lebensmittelretten.de/?p=botschafter">http://www.lebensmittelretten.de/botschafter</a>
										Gerne kannst du auch Deine Website, Projekt oder sonstiges
										erwähnen, was Du öffentlich an Informationen teilen möchtest,
										die vorteilhaft sind.'))
				,'Botschafter werden',array('class'=>'ui-padding')),
					
			
				v_field($db->getVal('body', 'document', 1).v_form_checkbox('aufgaben_botschafter',array('required'=>true,'values' => array(
					array('id'=>1,'name'=>'Ja dem Stimme ich zu')
				))), 'Aufgaben der BotschafterInnen',array('class'=>'ui-padding')),
					
				v_field($db->getVal('body', 'document', 11).v_form_checkbox('datenschutz',array('required'=>true,'values' => array(
					array('id'=>1,'name'=>'Ja dem Stimme ich zu')
				))), 'Aufgaben der BotschafterInnen',array('class'=>'ui-padding'))
			),array('submit'=>'Antrag auf Botschafterrolle verbindlich absenden'))
		);
	}

}