<?php

if(getAction('new'))
{
	handle_add();
	
	addBread(s('bread_betrieb'),'/?page=fsbetrieb');
	addBread(s('bread_new_betrieb'));
			
	$content = betrieb_form();

	$right = v_field(v_menu(array(
		pageLink('betrieb','back_to_overview')
	)),s('actions'));
}
elseif($id = getActionId('delete'))
{
	if($db->del_betrieb($id))
	{
		info(s('betrieb_deleted'));
		goPage();
	}
}
elseif($id = getActionId('edit'))
{
	handle_edit();
	
	addBread(s('bread_betrieb'),'/?page=fsbetrieb');
	addBread(s('bread_edit_betrieb'));
	
	$data = $db->getOne_betrieb($id);
	setEditData($data);
			
	$content = betrieb_form();
			
	$right = v_field(v_menu(array(
		pageLink('betrieb','back_to_overview')
	)),s('actions'));
}
else if(isset($_GET['id']))
{
	
	//$data = $db->getOne_betrieb($_GET['id']);	
	//addHead('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&amp;language=de"></script>');
	//addHead('<script type="text/javascript" src="js/gmap/gmap.js"></script>');
	
	$betrieb = $db->getBetrieb((int)$_GET['id']);
	
	addBread(s('bread_betrieb'),'/?page=fsbetrieb');
	addBread($betrieb['name']);
	
	$content = v_field(v_map($betrieb),v_getStatusAmpel($betrieb['betrieb_status_id']).' '.$betrieb['name']);
	
	$right = v_field('
	<div class="ui-padding">
		<p>'.$betrieb['ansprechpartner'].'</p>
		
		<p>'.$betrieb['str'].' '.$betrieb['hsnr'].'<br />
			DE '.$betrieb['plz'].' <br />
		</p>
		<p>
			<a href="mailto:'.$betrieb['email'].'">'.$betrieb['email'].'</a><br />
			'.$betrieb['telefon'].'<br />
			'.$betrieb['fax'].'
		</p>
	</div>
	', 'Ansprechpartner & Adresse');
	
	addHidden('<div id="dialog-comment"></div>');
	addJs('
	$("#dialog-comment").dialog({
		autoOpen:false,
		modal:true
	});');
	
	$items = array();
	foreach ($betrieb['notitzen'] as $n)
	{
		$items[] = array(
			'name' => dt($n['zeit_ts']),
			'click' => "showComment('".$n['id']."');"
		);
		addHidden('<input type="hidden" id="comment-title-'.$n['id'].'" value="'.dt($n['zeit_ts']).'" />');
		addHidden('<textarea id="comment-'.$n['id'].'" />'.nl2br($n['text']).'</textarea>');
	}
	$menu = v_menu($items);
	
	$right .= v_field($menu, 'Notizen');
}
else
{
	addBread(s('betrieb_bread'),'/?page=fsbetrieb');
	
	$right .= v_menu(array(
			array('click' => 'alert(0);','name' => 'Vertretung finden')
	),'Aktionen');
	$content = '';
	if($betriebe = $db->getFsBetriebe())
	{
		
		if(count($betriebe) > 0)
		{
			hiddenDialog('abholen', array(v_form_abhol_table(),v_form_hidden('bid', 0)),'Abholzeiten eintragen',array('reload' => true));
			hiddenDialog('abholer', array(v_form_hidden('bbdow', 0),v_form_hidden('bbid', 0),v_form_desc('abholerdesc', ''),v_form_select_foodsaver(array('nolabel'=>true))),'Abholer auswählen',array('reload' => true));
			foreach ($betriebe as $b)
			{
				$title = v_getStatusAmpel($b['betrieb_status_id']).' '.$b['name'].v_switch(array('Normal','Karte'));
				$cnt = '';
				if(empty($b['abholen']) && $b['betrieb_status_id'] == 3)
				{
					$cnt .= v_info('F&uuml;r '.$b['name'].' sind noch keine Abholzeiten eingetragen','Achtung!');
					if($b['own'])
					{
						$cnt .= '<div class="ui-padding-top">'.v_dialog_button('abholen','Abholzeiten jetzt eintragen',array('click'=>'$("#bid").val('.$b['id'].')')).'</div>';
					}
					else
					{
						// Kontaktiere den verantwortlichen...
					}
				}
				elseif($b['betrieb_status_id'] == 3)
				{
					$cnt = '';
					foreach ($b['abholen'] as $a)
					{
						$abholer_cnt = '';
						if($a['foodsaver_id'] == 0)
						{
							if($b['own'])
							{
								$abholer_cnt = ' <span class="abhol-button">'.v_dialog_button('abholer', 'Abholer festlegen',array('icon'=>'pencil','click'=>'$("#bbdow").val('.(int)$a['dow'].');$("#bbid").val('.$b['id'].');$("#abholerdesc").text(\'Wähle einen Abholer für '.format_day($a['dow']).' aus der Liste.\')')).'</span>';
							}
							else
							{
								$abholer_cnt = ' holt noch niemand ab';
							}
						}
						else
						{
							$abholer_cnt = ' von '.$a['foodsaver']['name'].' '.$a['foodsaver']['nachname'].' ';
							if($b['own'])
							{
								$abholer_cnt .= '<span class="abhol-button">'.v_dialog_button('abholer', 'Abholer ändern',array('notext'=>true,'icon'=>'pencil','click'=>'$("#bbdow").val('.(int)$a['dow'].');$("select#foodsaver option[value=\''.$a['foodsaver']['id'].'\']").attr("selected",true);$("#bbid").val('.$b['id'].');$("#abholerdesc").text(\'Wähle einen Abholer für '.format_day($a['dow']).' aus der Liste.\')')).'</span>';
							}
						}
						$cnt .= '<div class="abholzeit">'.format_day($a['dow']).'s um '.format_time($a['time']).$abholer_cnt.'</div>';
					}
					if($b['own'])
					{
						$cnt .= '<div class="ui-padding-top">'.v_dialog_button('abholen','Abholzeiten ändern',array('click'=>'$("#bid").val('.$b['id'].')','title' => 'Abholzeiten ändern')).'</div>';
					}
					
					$cnt = v_input_wrapper('Abholzeiten', $cnt, 'abholzeiten-view');
				}
				
				
				if(isset($_GET['v']) && $_GET['v'] == 'karte')
				{
					$cnt .= v_map($b,array('width'=>569));
				}
				else
				{
					$cnt .= v_input_wrapper('Ansprechpartner', $b['ansprechpartner']);
					$cnt .= v_input_wrapper('Anschrift', $b['anschrift'].', '.$b['plz']);
					$cnt .= v_input_wrapper('Telefon', $b['telefon']);
				}
				
				if($b['own'])
				{
					$cnt .= v_info('Für Diesen Betrieb bist Du verantwortlich','Hinweis!');
				}
				
				$content .= v_field($cnt, $title,array('class'=>'ui-padding'));
				//$content .= v_field('<pre>'.print_r($b,true).'</pre>',$b['name']);
			}
		}
		else
		{
			info('Du holst noch kein Essen ab.');
		}
		
	}
	else
	{
		info('Du holst noch kein Essen ab.');
	}
	
	/*
	if($data = $db->getBasics_betrieb())
	{
		$rows = array();
		foreach ($data as $d)
		{
					
			$rows[] = array(
				array('cnt' => '<a href="/?page=fsbetrieb&id='.$d['id'].'">'.$d['name'].'</a>'),
				array('cnt' => v_toolbar(array('id'=>$d['id'],'types' => array('edit','delete'),'confirmMsg'=>sv('delete_sure',$d['name'])))			
			));
		}
		
		$table = v_tablesorter(array(
			array('name' => s('name')),
			array('name' => s('actions'),'sort' => false,'width' => 50)
		),$rows);
		
		$content = v_field($table,'Alle betrieb');	
	}
	else
	{
		info(s('betrieb_empty'));		
	}
			
	$right = v_field(v_menu(array(
		array('href' => '/?page=fsbetrieb&a=neu','name' => s('neu_betrieb'))
	)),'Aktionen');
	*/
}					
function betrieb_form()
{
	global $db;
	
	$foodsaver_values = $db->getBasics_foodsaver();

	return v_quickform('betrieb',array(
	
			v_form_text('name'),
			v_form_text('plz'),
			v_form_text('str'),
			v_form_text('hsnr'),
			
			
			v_form_select('kette_id',array('add'=>true)),
			v_form_select('betrieb_kategorie_id',array('add'=>true)),
			
			v_form_select('betrieb_status_id'),
			
			v_form_text('ansprechpartner'),
			v_form_text('telefon'),
			v_form_text('fax'),
			v_form_text('email'),
			v_form_select('foodsaver',array('values' => $foodsaver_values))
	));
}

function handle_edit()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		$g_data['foodsaver'] = array($g_data['foodsaver']);
		if($db->update_betrieb($_GET['id'],$g_data))
		{
			info(s('betrieb_edit_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}
function handle_add()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		$g_data['foodsaver'] = array($g_data['foodsaver']);
		if($db->add_betrieb($g_data))
		{
			info(s('betrieb_add_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}
				
?>