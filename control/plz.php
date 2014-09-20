<?php
			
$db = new FoodsaverDb();

if(getAction('neu'))
{
	handle_add();
	
	addBread(s('bread_plz'),'?page=plz');
	addBread(s('bread_new_plz'));
			
	$content = plz_form();

	$right = v_field(v_menu(array(
		pageLink('plz','back_to_overview')
	)),s('actions'));
}
elseif($id = getActionId('delete'))
{
	if($db->del_plz($id))
	{
		info(s('plz_deleted'));
		goPage();
	}
}
elseif($id = getActionId('edit'))
{
	handle_edit();
	
	addBread(s('bread_plz'),'?page=plz');
	addBread(s('bread_edit_plz'));
	
	$data = $db->getOne_plz($id);
	setEditData($data);
			
	$content = plz_form();
			
	$right = v_field(v_menu(array(
		pageLink('plz','back_to_overview')
	)),s('actions'));
}
else if(isset($_GET['id']))
{
	$data = $db->getOne_plz($_GET['id']);	
	print_r($data);	
}
else
{
	addBread(s('plz_bread'),'?page=plz');
	
	if($data = $db->getBasics_plz())
	{
		$rows = array();
		foreach ($data as $d)
		{
					
			$rows[] = array(
				array('cnt' => '<a class="linkrow ui-corner-all" href="?page=plz&id='.$d['id'].'">'.$d['name'].'</a>'),
				array('cnt' => v_toolbar(array('id'=>$d['id'],'types' => array('edit','delete'),'confirmMsg'=>sv('delete_sure',$d['name'])))			
			));
		}
		
		$table = v_tablesorter(array(
			array('name' => s('name')),
			array('name' => s('actions'),'sort' => false,'width' => 50)
		),$rows);
		
		$content = v_field($table,'Alle plz');	
	}
	else
	{
		info(s('plz_empty'));		
	}
			
	$right = v_field(v_menu(array(
		array('href' => '?page=plz&a=neu','name' => s('neu_plz'))
	)),'Aktionen');
}					
function plz_form()
{
	global $db;
	
			
	return v_quickform('plz',array(
		
		v_form_select('stadt_id'),
		v_form_select('bezirk_id'),
		v_form_text('name')	
	));
}

function handle_edit()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		if($db->update_plz($_GET['id'],$g_data))
		{
			info(s('plz_edit_success'));
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
		if($db->add_plz($g_data))
		{
			info(s('plz_add_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}
				
?>