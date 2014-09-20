<?php
if(!S::may('orga'))
{
	go('/');
}

if(getAction('neu'))
{
	handle_add();
	
	addBread(s('bread_message_tpl'),'?page=message_tpl');
	addBread(s('bread_new_message_tpl'));
			
	addContent(message_tpl_form());

	addContent(v_field(v_menu(array(
		pageLink('message_tpl','back_to_overview')
	)),s('actions')),CNT_RIGHT);
}
elseif($id = getActionId('delete'))
{
	if($db->del_message_tpl($id))
	{
		info(s('message_tpl_deleted'));
		goPage();
	}
}
elseif($id = getActionId('edit'))
{
	handle_edit();
	
	addBread(s('bread_message_tpl'),'?page=message_tpl');
	addBread(s('bread_edit_message_tpl'));
	
	$data = $db->getOne_message_tpl($id);
	setEditData($data);
			
	addContent(message_tpl_form());
			
	addContent(v_field(v_menu(array(
		pageLink('message_tpl','back_to_overview')
	)),s('actions')),CNT_RIGHT);
}
else if(isset($_GET['id']))
{
	$data = $db->getOne_message_tpl($_GET['id']);	
	print_r($data);	
}
else
{
	addBread(s('message_tpl_bread'),'?page=message_tpl');
	
	if($data = $db->getBasics_message_tpl())
	{
		$rows = array();
		foreach ($data as $d)
		{
					
			$rows[] = array(
				array('cnt'=>$d['id']),
				array('cnt' => '<a class="linkrow ui-corner-all" href="?page=message_tpl&a=edit&id='.$d['id'].'">'.$d['name'].'</a>')		
			);
		}
		
		$table = v_tablesorter(array(
			array('name' => 'ID','width'=>30),
			array('name' => s('name'))
		),$rows);
		
		addContent(v_field($table,'Alle E-Mail Vorlagen'));	
	}
	else
	{
		info(s('message_tpl_empty'));		
	}
			
	addContent(v_field(v_menu(array(
		array('href' => '?page=message_tpl&a=neu','name' => s('neu_message_tpl'))
	)),'Aktionen'),CNT_RIGHT);
}					
function message_tpl_form()
{
	global $db;
	global $g_data;
	$g_data['language_id'] = 1;
	$kennung = '';
	if(isset($g_data['id']))
	{
		$kennung = v_input_wrapper('Kennung', $g_data['id']);
	}	
	//addJs('$("#name").bind("blur keyup",function(){this.value=this.value.toLowerCase().replace(\' \',\'_\');this.value=this.value.replace(/[^a-z_]/g,\'\');});');
	return v_form('E-Mail Vorlage', array(
			v_field(
					v_form_select('language_id').
					v_form_text('name',array('required'=>true)).
					v_form_text('subject',array('required' => array())).
					v_form_file('attachement'),
						
					'E-Mail Vorlage',
					array('class'=>'ui-padding')
			),
			v_field(v_form_tinymce('body',array('nowrapper'=>true)), s('message'))
	),array('submit'=>'Speichern'));
	/*
	return v_quickform('E-Mail Vorlagen',array(
		$kennung,
		v_form_select('language_id'),
		v_form_text('name',array('required'=>true)),
		v_form_text('subject',array('required'=>true)),
		v_form_textarea('body',array('required'=>true)),
	),array('submit' => 'Speichern'));*/
}

function handle_edit()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		if($db->update_message_tpl($_GET['id'],$g_data))
		{
			info(s('message_tpl_edit_success'));
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
		if($db->add_message_tpl($g_data))
		{
			info(s('message_tpl_add_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}
				
?>