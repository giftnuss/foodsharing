<?php
require_once 'lang/DE/upgrade.lang.php';
if((isBotschafter() || isOrgaTeam()) && isset($_GET['fid']))
{
	$id = (int)$_GET['fid'];

	if($req = $db->qRow('SELECT * FROM '.PREFIX.'upgrade_request WHERE foodsaver_id = '.(int)$id))
	{
		if($fs = $db->qRow('SELECT `id`,`rolle`,UNIX_TIMESTAMP(`anmeldedatum`) AS anmeldedatum,`geschlecht`,`bezirk_id`, `geschlecht`,`name`,`nachname`,`anschrift`,`plz`,`stadt`,`email`,`telefon`,`handy`,`geb_datum`,`photo` FROM `'.PREFIX.'foodsaver` WHERE `id` = '.(int)$_GET['fid']))
		{
			$bezirk = $db->getBezirk($req['bezirk_id']);
			$reqdata = json_decode($req['data'],true);
			if(isset($_GET['activate']))
			{
				$db->add_botschafter(array(
						'foodsaver_id' => $fs['id'],
						'bezirk_id' => $req['bezirk_id']
				));
				
				$db->insert('
					INSERT INTO '.PREFIX.'partnerschaft
					(`foodsaver_id`,`partner_id`,`form`,`time`)
					VALUES
					('.(int)fsid().','.(int)$fs['id'].',2,NOW())		
				');
				
				$db->update('
					UPDATE '.PREFIX.'foodsaver 
					SET  rolle = 3, 
						 `about_me_public` = '.$db->strval($reqdata['about_me_public']).',
						 photo_public = 1
					WHERE `id` = '.(int)$fs['id']);
				
				$db->del('DELETE FROM '.PREFIX.'upgrade_request WHERE foodsaver_id = '.(int)$id);
				
				$db->addGlocke($id, 'Botschafter '.$bezirk['name'],'Du bist jetzt Botschafter/in für '.$bezirk['name'],'?page=relogin');
				
				info(sv('user_is_botschafter_now',$fs['name']));
				goPage('dashboard');
			}
			else
			{
				
				
				/*
				 * Array
					(
					    [form_submit] => upbotsch
					    [bezirk] => 4
					    [new_bezirk] => 
					    [time] => 5
					    [photo_public] => 1
					    [tel_public] => Array
					        (
					            [0] => 1
					        )
					
					    [about_me_public] => dfgdfg
					    [aufgaben_botschafter] => Array
					        (
					            [0] => 1
					        )
					
					    [datenschutz] => Array
					        (
					            [0] => 1
					        )
					
					)
				 */
				
				addContent(v_field(v_info(s('upgrade_partner_info')),s('info')));
				
				$details = v_input_wrapper('Botschafter anfrage für', $bezirk['name']);
				
				if(!empty($reqdata['new_bezirk']))
				{
					$details .= v_input_wrapper($fs['name'].' wünscht sich einen neuen Bezirk', strip_tags($reqdata['new_bezirk']));
				}
				
				$details .= v_input_wrapper('Kurzbeschreiben', strip_tags($reqdata['about_me_public']));
				
				addBread(s('new_upgrade'));
			
				addContent(v_field(
					$details, $fs['name'].' '.$fs['nachname'].' will '.getRolle($fs['geschlecht'],2).' f&uuml;r '.$bezirk['name'].' werden.',array('class'=>'ui-padding'))
				);
			
				addContent(v_field(
						
						v_input_wrapper('Anmeldezeitpunkt', format_dt($fs['anmeldedatum'])).
						v_input_wrapper('Name', $fs['name']).
						v_input_wrapper('Nachname', $fs['nachname']).
						v_input_wrapper('Geschlecht', genderWord($fs['geschlecht'],'Mann','Frau','Anderes / Sonstiges')).
						v_input_wrapper('Anschrift', $fs['anschrift']).
						v_input_wrapper('Postleitzahl', $fs['plz']).
						v_input_wrapper('Stadt', $fs['stadt']).
						v_input_wrapper('E-Mail Adresse', $fs['email']).
						v_input_wrapper('Telefon', $fs['telefon']).
						v_input_wrapper('Handy', $fs['handy']).
						v_input_wrapper('Geburtstag', $fs['geb_datum'])
							
						//v_accordion(array(array('name'=> 'Details aus Botschafter-Anmeldung anzeigen','cnt' => $details)))
							
						,$fs['name'].'s Daten',array('class'=>'ui-padding'))
				);
			
				addContent(
					v_field('<div align="center" style="padding-top:5px;padding-bottom:5px;"><img src="'.imgPortait($fs['photo']).'" /></div>', 'Foto')
				,CNT_RIGHT);
			
				addContent(v_menu(array(
						array('href'=>'?page=checkUpgradeRequest&fid='.$id.'&activate=1','name'=>$fs['name'].' als '.getRolle($fs['geschlecht'],2).' Freischalten und Partnerschaft übernehmen'),
						/*	array('click'=>'ifconfirm(\'?page=checkReg&id='.$id.'&delete=1\',\'Willst Du '.$fs['name'].' wirklich Ablehnen?\',\''.$fs['name'].' Ablehnen\')','name'=>'Antrag Ablehnen'), */
						array('click'=>'chat('.(int)$fs['id'].');return false;','name'=>$fs['name'].' Eine Nachricht schreiben')
				),s('options')),CNT_RIGHT);
			}
		}
	}
}
else
{
	goPage('dashboard');
}


