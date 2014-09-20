<?php

$bezirk = $db->getBezirk();

if(getAction('neu'))
{
	handle_add();
	
	addBread(s('bread_blog_entry'),'?page=blog_entry');
	addBread(s('bread_new_blog_entry'));
	
	addContent(blog_entry_form());

	addContent(v_field(v_menu(array(
		pageLink('blog_entry','back_to_overview')
	)),s('actions')),CNT_LEFT);
}
elseif($id = getActionId('delete'))
{
	if($db->del_blog_entry($id))
	{
		info(s('blog_entry_deleted'));
		goPage('blog');
	}
}
elseif($id = getActionId('edit'))
{
	handle_edit();
	
	addBread(s('bread_blog_entry'),'?page=blog_entry');
	addBread(s('bread_edit_blog_entry'));
	
	$data = $db->getOne_blog_entry($id);
	setEditData($data);
			
	addContent(blog_entry_form());
			
	addContent(v_field(v_menu(array(
		pageLink('blog_entry','back_to_overview')
	)),s('actions')),CNT_LEFT);
}
else if(isset($_GET['id']))
{
	$data = $db->getOne_blog_entry($_GET['id']);	
	print_r($data);	
}
else
{
	addBread(s('blog_entry_bread'),'?page=blog_entry');
	
	$title = 'Alle Artikel aus '.$bezirk['name'];
	
	if(isOrgaTeam())
	{
		$title = 'alle Artikel';
	}
	
	if($data = $db->listArticle($bezirk['id']))
	{
		$rows = array();
		foreach ($data as $d)
		{
			$row_tmp = array();
			
			$row_tmp[] = array('cnt' => v_activeSwitcher('blog_entry',$d['id'],$d['active']));
			
			$row_tmp[] = array('cnt' => '<a class="linkrow ui-corner-all" href="?page=blog_entry&a=edit&id='.$d['id'].'">'.format_d($d['time_ts']).'</a>');
			$row_tmp[] = array('cnt' => '<a class="linkrow ui-corner-all" href="?page=blog_entry&a=edit&id='.$d['id'].'">'.$d['name'].'</a>');
			$row_tmp[] = array('cnt' => v_toolbar(array('id'=>$d['id'],'types' => array('edit','delete'),'confirmMsg'=>sv('delete_sure',$d['name']))));
			
			
			$rows[] = $row_tmp;
		}
		
		$theads = array();
		
		$theads[] = array('name'=>s('active'),'sort' => false,'width' => 90);
		$theads[] = array('name' => s('date'));
		$theads[] = array('name' => s('name'));
		$theads[] = array('name' => s('actions'),'sort' => false,'width' => 50);
		
		$table = v_tablesorter($theads,$rows);
		
		
		addContent(v_field($table,$title));	
	}
	else
	{
		info(s('blog_entry_empty'));		
	}
			
	addContent(v_field(v_menu(array(
		array('href' => '?page=blog_entry&a=neu','name' => s('neu_blog_entry'))
	)),'Aktionen'),CNT_LEFT);
}					
function blog_entry_form()
{
	global $db;	
	
	return v_form('test', array(
		v_field(
			v_form_text('name').v_form_textarea('teaser',array('style'=>'height:75px;')).
			v_form_picture('picture',array('resize'=>array(250,528),'crop'=>array((250/135),(528/170)))),

			s('neu_blog_entry'),
			array('class'=>'ui-padding')
		
		),
		v_field(v_form_tinymce('body',array('nowrapper'=>true,'public_content'=>true)), 'Inhalt')
	));
}

function handle_edit()
{
	global $db;
	global $g_data;
	if(submitted())
	{
		
		
		$data = $db->getValues(array('time','foodsaver_id','bezirk_id'), 'blog_entry', $_GET['id']);
		
		$g_data['bezirk_id'] = $data['bezirk_id'];
		$g_data['foodsaver_id'] = $data['foodsaver_id'];
		$g_data['time'] = $data['time'];
		
		
		if($db->update_blog_entry($_GET['id'],$g_data))
		{
			info(s('blog_entry_edit_success'));
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
		$g_data['bezirk_id'] = getBezirkId();
		$g_data['foodsaver_id'] = fsId();
		$g_data['time'] = date('Y-m-d H:i:s');
		
		if($db->add_blog_entry($g_data))
		{
			info(s('blog_entry_add_success'));
			goPage();
		}
		else
		{
			error(s('error'));
		}
	}
}
				
?>