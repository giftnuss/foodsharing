<?php
if(isBotschafter() && isset($_GET['id']))
{
	$id = (int)$_GET['id'];
	
	$fs = $db->getReg($id);
	
	if(isset($_GET['activate']))
	{
		$myid = fsid();
		$db->activateUser($id,$fs['rolle']);
		$db->insert('
			INSERT INTO '.PREFIX.'partnerschaft
			(`foodsaver_id`,`partner_id`,`form`,`time`)
			VALUES
			('.(int)$myid.','.(int)$fs['id'].',1,NOW())
		');
		info(s('user_is_activated'));
		addJs('profile('.(int)$id.');');
	}
	
	if(isset($_GET['delete']))
	{
		$fs = $db->getOne_foodsaver($id);
		
		tplMail(8, $fs['email'],array(
			'name' => $fs['name'],
			'anrede' => gAnrede($fs['geschlecht'])
		));
		logDel($fs);
		$db->del_foodsaver($id);
		info(s('user_is_deleted'));
		goPage();
	}
	
	
	
	$fs['data'] = json_decode($fs['data'],true);
	
	addBread(s('new_registration'));
	
	$bezirk = $db->getBezirk($fs['bezirk_id']);
	
	$details = '';
	
	foreach ($fs['data'] as $i => $d)
	{
		$details .= v_input_wrapper($i, $d);
	}
	
	addContent(v_field(
			v_input_wrapper('Anmeldezeitpunkt', format_dt($fs['anmeldedatum'])).
			v_input_wrapper('Name', $fs['name']).
			v_input_wrapper('Nachname', $fs['nachname']).
			v_input_wrapper('Geschlecht', genderWord($fs['geschlecht'],'Mann','Frau','Anderes / Sonstiges')).
			v_input_wrapper('Anschrift', $fs['anschrift']).
			v_input_wrapper('Postleitzahl', $fs['plz']).
			v_input_wrapper('Stadt', $fs['data']['stadt']).
			v_input_wrapper('Land', $fs['data']['land']).
			v_input_wrapper('E-Mail Adresse', $fs['data']['email']).
			v_input_wrapper('Telefon', $fs['data']['festnetz']).
			v_input_wrapper('Handy', $fs['data']['handy']).
			v_input_wrapper('Geburtstag', $fs['data']['geb_datum']).
			v_input_wrapper('Motivationstext', $fs['data']['motivation']).
			v_input_wrapper('Beruf / Berufung', $fs['data']['berufung']).
			
			v_accordion(array(array('name'=> 'Details anzeigen','cnt' => $details)))
			
			,$fs['name'].' '.$fs['nachname'].' will '.getRolle($fs['geschlecht'],$fs['rolle']).' f&uuml;r '.$bezirk['name'].' werden.',array('class'=>'ui-padding'))
	);
	
	addContent(
		v_field('<div class="ui-padding" align="center"><img src="'.imgPortait($fs['photo']).'" /></div>', 'Foto'),
	CNT_RIGHT);
	
	addContent(v_menu(array(
		array('href'=>'/?page=checkReg&id='.$id.'&activate=1','name'=>$fs['name'].' Freischalten'),
		array('click'=>'ifconfirm(\'/?page=checkReg&id='.$id.'&delete=1\',\'Willst Du '.$fs['name'].' wirklich Ablehnen?\',\''.$fs['name'].' Ablehnen\')','name'=>$fs['name'].' Ablehnen')
	),s('options')),CNT_RIGHT);
}
else
{
	addBread(s('new_registration'));
}


