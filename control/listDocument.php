<?php
$rolle = 1;
if(isBotschafter())
{
	$rolle = 2;
}
if(isOrgaTeam())
{
	$rolle = 3;
}

if(isset($_GET['id']) && isset($_GET['a']) && $_GET['a'] == 'dl')
{
	if($res = $db->getOne_document($_GET['id']))
	{
		if($res['rolle'] <= $rolle)
		{
			$file = json_decode($res['file'],true);
			$ext =explode('.', $file['uname']);
			$ext = end($ext);
			
			$mm_type="application/octet-stream";

			header("Cache-Control: public, must-revalidate");
			header("Pragma: hack");
			header("Content-Type: " . $mm_type);
			header("Content-Length: " .(string)($file['size']) );
			header('Content-Disposition: attachment; filename="'.id($res['name']).'.'.$ext.'"');
			header("Content-Transfer-Encoding: binary\n");
			
			
			
			readfile($file['path']);
			exit();
		}
	}
	
	goPage('listDocument');
}
elseif(isset($_GET['id']))
{
	
	if($res = $db->getOne_document($_GET['id']))
	{
		if($res['file']== 'false')
		{
			$res['file'] = '';
		}
		if(empty($res['body']) && !empty($res['file']))
		{
			go('?page=listDocument&id='.$res['id'].'&a=dl');
		}
		addBread('Dokumente','?page=document');
		addBread(substr($res['name'],0,30));
		
		$cnt = '';
		if(!empty($res['file']))
		{
			addJs('$(".dl-button").button();');
			
			addContent(v_field('<div class="ui-padding" style="text-align:center;"><a class="dl-button" href="?page=listDocument&id='.$res['id'].'&a=dl">Download</a></div>', 'Aktion'),CNT_RIGHT);
		}
		if(!empty($res['body']))
		{
			$cnt .= $res['body'];
		}
		
		addContent(v_field($cnt, $res['name'],array('class'=>'ui-padding')));
	}
	else
	{
		goPage('listDocument');
	}
}
else
{

	addBread('Dokumente','?page=document');
	
	
	
	$docs = $db->getDocuments($rolle);
	$menu = array();
	foreach ($docs as $d)
	{
		$menu[] = array(
			'href' => '?page=listDocument&id='.$d['id'],
			'name' => $d['name']
		);
	}
	
	addContent(v_menu($menu,'Dokumente'));
}