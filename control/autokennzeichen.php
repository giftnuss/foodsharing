<?php

if(getAction('neu'))
{
	handle_add();
	
	addBread(s('bread_autokennzeichen'),'/?page=autokennzeichen');
	addBread(s('bread_new_autokennzeichen'));
			
	addCOntent(autokennzeichen_form());

	addContent(v_field(v_menu(array(
		pageLink('autokennzeichen','back_to_overview')
	)),s('actions')),CNT_RIGHT);
}
elseif($id = getActionId('delete'))
{
	if($db->del_autokennzeichen($id))
	{
		info(s('autokennzeichen_deleted'));
		goPage();
	}
}
elseif($id = getActionId('edit'))
{
	handle_edit();
	
	addBread(s('bread_autokennzeichen'),'/?page=autokennzeichen');
	addBread(s('bread_edit_autokennzeichen'));
	
	$data = $db->getOne_autokennzeichen($id);
	setEditData($data);
			
	addContent(autokennzeichen_form());
			
	addContent(v_field(v_menu(array(
		pageLink('autokennzeichen','back_to_overview')
	)),s('actions')),CNT_RIGHT);
}
else if(isset($_GET['id']))
{
	$data = $db->getOne_autokennzeichen($_GET['id']);	
	print_r($data);	
}
else
{
	addBread(s('autokennzeichen_bread'),'/?page=autokennzeichen');
	
	if($data = $db->get_autokennzeichen())
	{
		$rows = array();
		foreach ($data as $d)
		{
					
			$rows[] = array(
				array('cnt' => '<a class="linkrow ui-corner-all" href="/?page=autokennzeichen&a=edit&id='.$d['id'].'">'.$d['name'].' - '.$d['title'].'</a>'),
				array('cnt' => v_toolbar(array('id'=>$d['id'],'types' => array('edit','delete'),'confirmMsg'=>sv('delete_sure',$d['name'])))			
			));
		}
		
		$table = v_tablesorter(array(
			array('name' => s('name')),
			array('name' => s('actions'),'sort' => false,'width' => 50)
		),$rows);
		
		addContent(v_field($table,'Alle KFZ-Kennzeichen'));	
	}
	else
	{
		info(s('autokennzeichen_empty'));		
	}
			
	addContent(v_field(v_menu(array(
		array('href' => '/?page=autokennzeichen&a=neu','name' => s('neu_autokennzeichen'))
	)),'Aktionen'),CNT_RIGHT);
}					
function autokennzeichen_form()
{
	global $db;
	
			
	return v_quickform('autokennzeichen',array(
		
		v_form_text('name'),
		v_form_text('title')	
	));
}

function handle_edit()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		if($db->update_autokennzeichen($_GET['id'],$g_data))
		{
			info(s('autokennzeichen_edit_success'));
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
		if($db->add_autokennzeichen($g_data))
		{
			info(s('autokennzeichen_add_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}
				
?>