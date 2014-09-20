<?php 
function handleNewbetrieb()
{	
	$data = getPostData();
	
	global $db;
	if($db->addBetrieb($data))
	{
		$db->relogin();
		info('Betrieb wurde erfolgreich eingetragen!');
		return true;
	}
	return false;
}

function handleEditBetrieb()
{
	$data = getPostData();
	global $db;
	
	if($db->editBetrieb($data,$_GET['id']))
	{
		$db->relogin();
		info('&Auml;nderungen wurden erfolgreich gespeichert!');
		return true;
	}
	return false;
}




?>