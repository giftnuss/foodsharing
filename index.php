<?php 
/*
if(isset($_GET['g_path']))
{
	$path = explode('/', $_GET['g_path']);
	//print_r($path);
	
	
	switch ($path[0])
	{
		case 'group' :
			$_GET['page'] = 'bezirk';
			$_GET['bid'] = $path[1];
			$_GET['sub'] = $path[2];
			break;
			
		default:
			break;
	}
	
}
*/
require_once 'lib/inc.php';


//importUsers();

getCurrent();
$menu = getMenu();

getMessages();
makeHead();

if(isset($_POST['form_submit']))
{
	if(handleForm($_POST['form_submit']))
	{
		go('?page='.getPage());
	}
}
$msgbar = '';
$logolink = '/';
if(S::may())
{
	$msgbar = v_msgBar();
	$logolink = '/?page=dashboard';
}

include 'tpl/'.$g_template.'.php';
?>