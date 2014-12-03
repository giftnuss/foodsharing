<?php
if(!S::may('orga'))
{
	go('/');
}

if(getAction('neu'))
{
	handle_add();
	
	addBread(s('bread_lebensmittel'),'?page=lebensmittel');
	addBread(s('bread_new_lebensmittel'));
			
	addContent(lebensmittel_form());

	addContent(v_field(v_menu(array(
		pageLink('lebensmittel','back_to_overview')
	)),s('actions')),CNT_RIGHT);
}
elseif($id = getActionId('delete'))
{
	if($db->del_lebensmittel($id))
	{
		info(s('lebensmittel_deleted'));
		goPage();
	}
}
elseif($id = getActionId('edit'))
{
	handle_edit();
	
	addBread(s('bread_lebensmittel'),'?page=lebensmittel');
	addBread(s('bread_edit_lebensmittel'));
	
	$data = $db->getOne_lebensmittel($id);
	setEditData($data);
			
	addContent(lebensmittel_form());
			
	addContent(v_field(v_menu(array(
		pageLink('lebensmittel','back_to_overview')
	)),s('actions')),CNT_RIGHT);
}
else if(isset($_GET['id']))
{
	$data = $db->getOne_lebensmittel($_GET['id']);	
	print_r($data);	
}
else
{
	addBread(s('lebensmittel_bread'),'?page=lebensmittel');
	
	if($data = $db->getBasics_lebensmittel())
	{
		$rows = array();
		foreach ($data as $d)
		{
					
			$rows[] = array(
				array('cnt' => '<a class="linkrow ui-corner-all" href="/?page=lebensmittel&id='.$d['id'].'">'.$d['name'].'</a>'),
				array('cnt' => v_toolbar(array('id'=>$d['id'],'types' => array('edit','delete'),'confirmMsg'=>sv('delete_sure',$d['name'])))			
			));
		}
		
		$table = v_tablesorter(array(
			array('name' => s('name')),
			array('name' => s('actions'),'sort' => false,'width' => 50)
		),$rows);
		
		addContent(v_field($table,'Alle lebensmittel'));	
	}
	else
	{
		info(s('lebensmittel_empty'));		
	}
			
	addContent(v_field(v_menu(array(
		array('href' => '?page=lebensmittel&a=neu','name' => s('neu_lebensmittel'))
	)),'Aktionen'),CNT_RIGHT);
}					
function lebensmittel_form()
{
	global $db;
			
	return v_quickform('lebensmittel',array(
		
		v_form_text('name')
	));
}

function handle_edit()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		if($db->update_lebensmittel($_GET['id'],$g_data))
		{
			info(s('lebensmittel_edit_success'));
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
		if($db->add_lebensmittel($g_data))
		{
			info(s('lebensmittel_add_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}
				
?>