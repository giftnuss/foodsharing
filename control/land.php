<?php

if(getAction('neu'))
{
	handle_add();
	
	addBread(s('bread_land'),'?page=land');
	addBread(s('bread_new_land'));
			
	$content = land_form();

	$right = v_field(v_menu(array(
		pageLink('land','back_to_overview')
	)),s('actions'));
}
elseif($id = getActionId('delete'))
{
	if($db->del_land($id))
	{
		info(s('land_deleted'));
		goPage();
	}
}
elseif($id = getActionId('edit'))
{
	handle_edit();
	
	addBread(s('bread_land'),'?page=land');
	addBread(s('bread_edit_land'));
	
	$data = $db->getOne_land($id);
	setEditData($data);
			
	$content = land_form();
			
	$right = v_field(v_menu(array(
		pageLink('land','back_to_overview')
	)),s('actions'));
}
else if(isset($_GET['id']))
{
	$data = $db->getOne_land($_GET['id']);	
	print_r($data);	
}
else
{
	addBread(s('land_bread'),'?page=land');
	
	if($data = $db->getBasics_land())
	{
		$rows = array();
		foreach ($data as $d)
		{
					
			$rows[] = array(
				array('cnt' => '<a class="linkrow ui-corner-all" href="?page=land&id='.$d['id'].'">'.$d['name'].'</a>'),
				array('cnt' => v_toolbar(array('id'=>$d['id'],'types' => array('edit','delete'),'confirmMsg'=>sv('delete_sure',$d['name'])))			
			));
		}
		
		$table = v_tablesorter(array(
			array('name' => s('name')),
			array('name' => s('actions'),'sort' => false,'width' => 50)
		),$rows);
		
		$content = v_field($table,'Alle land');	
	}
	else
	{
		info(s('land_empty'));		
	}
			
	$right = v_field(v_menu(array(
		array('href' => '?page=land&a=neu','name' => s('neu_land'))
	)),'Aktionen');
}					
function land_form()
{
	global $db;
	
			
	return v_quickform('land',array(
		
		v_form_text('name')	
	));
}

function handle_edit()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		if($db->update_land($_GET['id'],$g_data))
		{
			info(s('land_edit_success'));
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
		if($db->add_land($g_data))
		{
			info(s('land_add_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}
				
?>